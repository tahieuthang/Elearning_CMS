<?php

namespace App\Services\LearningStreak;

use App\Models\CourseVideo;
use App\Models\CustomerLearningStreak;
use App\Models\CustomerLearningWeek;
use App\Models\CustomerVideoWeeklyProgress;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LearningStreakService
{
    public function __construct(private readonly TrackingSessionService $sessions) {}

    public function startVisit(int $customerId, int $courseId): array
    {
        return DB::transaction(function () use ($customerId, $courseId) {
            $session = $this->sessions->start($customerId, $courseId);
            $window = WeekWindow::from($session->started_at);
            $week = $this->lockedWeek($customerId, $window->startDate->toDateString());
            if (!$week->visit_completed_at) {
                $week->visit_completed_at = $session->started_at;
                $week->save();
            }
            $this->qualifyIfReady($customerId, $week, $window);

            return array_merge(['tracking_session' => $this->sessionData($session)], $this->summary($customerId, $window));
        });
    }

    public function recordRanges(int $customerId, CourseVideo $video, array $ranges, string $sessionId, string $capturedAt, ?int $fallbackDuration = null): array
    {
        if (count($ranges) > (int) config('learning_streak.max_ranges_per_request')) {
            throw new InvalidArgumentException('Too many watched ranges.');
        }

        return DB::transaction(function () use ($customerId, $video, $ranges, $sessionId, $capturedAt, $fallbackDuration) {
            $session = $this->sessions->validate($sessionId, $customerId, $video->course_id, $capturedAt);
            $duration = $this->resolveDuration($video, $fallbackDuration);
            $captured = CarbonImmutable::parse($capturedAt)->setTimezone(config('learning_streak.timezone'));
            $window = WeekWindow::from($captured);
            $weekStart = $window->startDate->toDateString();

            $videoWeek = CustomerVideoWeeklyProgress::where([
                'customer_id' => $customerId,
                'course_video_id' => $video->id,
                'week_start' => $weekStart,
            ])->lockForUpdate()->first();

            if (!$videoWeek) {
                $videoWeek = CustomerVideoWeeklyProgress::create([
                    'customer_id' => $customerId,
                    'course_id' => $video->course_id,
                    'course_video_id' => $video->id,
                    'week_start' => $weekStart,
                    'watched_ranges' => [],
                    'watched_seconds' => 0,
                ]);
                $videoWeek = CustomerVideoWeeklyProgress::whereKey($videoWeek->id)->lockForUpdate()->firstOrFail();
            }

            $merged = RangeMerger::merge($videoWeek->watched_ranges ?? [], $ranges, $duration);
            $videoWeek->watched_ranges = $merged['ranges'];
            $videoWeek->watched_seconds = $merged['total_seconds'];
            $videoWeek->save();

            $week = $this->lockedWeek($customerId, $weekStart);
            if ($merged['new_seconds'] > 0) {
                $week->watched_seconds += $merged['new_seconds'];
                if (!$week->watch_completed_at && $week->watched_seconds >= (int) config('learning_streak.watch_target_seconds')) {
                    $week->watch_completed_at = $captured;
                }
                $week->save();
            }
            $this->qualifyIfReady($customerId, $week, $window);

            $session->last_seen_at = now();
            $session->save();

            return $this->summary($customerId, $window);
        });
    }

    public function summary(int $customerId, ?WeekWindow $window = null): array
    {
        $window ??= WeekWindow::from();
        $streak = $this->reconcileStreak($customerId, $window);
        $week = CustomerLearningWeek::firstOrCreate([
            'customer_id' => $customerId,
            'week_start' => $window->startDate->toDateString(),
        ]);

        return [
            'week' => [
                'start' => $window->startDate->toDateString(),
                'end' => $window->endDate->toDateString(),
                'visit_completed' => (bool) $week->visit_completed_at,
                'watched_seconds' => (int) $week->watched_seconds,
                'watch_target_seconds' => (int) config('learning_streak.watch_target_seconds'),
                'watch_completed' => (bool) $week->watch_completed_at,
                'qualified' => (bool) $week->qualified_at,
            ],
            'streak' => [
                'current' => (int) $streak->current_streak,
                'longest' => (int) $streak->longest_streak,
                'last_qualified_week' => $streak->last_qualified_week?->toDateString(),
            ],
        ];
    }

    private function lockedWeek(int $customerId, string $weekStart): CustomerLearningWeek
    {
        $week = CustomerLearningWeek::where(['customer_id' => $customerId, 'week_start' => $weekStart])->lockForUpdate()->first();
        if ($week) {
            return $week;
        }

        CustomerLearningWeek::firstOrCreate(['customer_id' => $customerId, 'week_start' => $weekStart]);
        return CustomerLearningWeek::where(['customer_id' => $customerId, 'week_start' => $weekStart])->lockForUpdate()->firstOrFail();
    }

    private function qualifyIfReady(int $customerId, CustomerLearningWeek $week, WeekWindow $window): void
    {
        if ($week->qualified_at || !$week->visit_completed_at || !$week->watch_completed_at) {
            return;
        }

        $week->qualified_at = now();
        $week->save();
        $streak = CustomerLearningStreak::where('customer_id', $customerId)->lockForUpdate()->first();
        if (!$streak) {
            CustomerLearningStreak::create(['customer_id' => $customerId]);
            $streak = CustomerLearningStreak::where('customer_id', $customerId)->lockForUpdate()->firstOrFail();
        }

        $weekStart = $window->startDate->toDateString();
        if ($streak->last_qualified_week?->toDateString() === $weekStart) {
            return;
        }
        $previous = $window->startDate->subWeek()->toDateString();
        $streak->current_streak = $streak->last_qualified_week?->toDateString() === $previous
            ? $streak->current_streak + 1
            : 1;
        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_qualified_week = $weekStart;
        $streak->save();
    }

    private function reconcileStreak(int $customerId, WeekWindow $window): CustomerLearningStreak
    {
        return DB::transaction(function () use ($customerId, $window) {
            $streak = CustomerLearningStreak::where('customer_id', $customerId)->lockForUpdate()->first();
            if (!$streak) {
                $streak = CustomerLearningStreak::create(['customer_id' => $customerId]);
                return $streak;
            }
            $last = $streak->last_qualified_week?->toDateString();
            $previous = $window->startDate->subWeek()->toDateString();
            $current = $window->startDate->toDateString();
            if ($last && $last !== $current && $last !== $previous && $streak->current_streak !== 0) {
                $streak->current_streak = 0;
                $streak->save();
            }
            return $streak;
        });
    }

    private function resolveDuration(CourseVideo $video, ?int $fallbackDuration): int
    {
        $duration = (int) $video->duration_seconds;
        if ($duration > 0) {
            return $duration;
        }
        if (!$fallbackDuration || $fallbackDuration <= 0) {
            throw new InvalidArgumentException('Video duration is unavailable.');
        }
        $video->duration_seconds = $fallbackDuration;
        $video->save();
        return $fallbackDuration;
    }

    private function sessionData($session): array
    {
        return [
            'id' => $session->id,
            'server_started_at' => $session->started_at->toIso8601String(),
            'expires_at' => $session->expires_at->toIso8601String(),
        ];
    }
}
