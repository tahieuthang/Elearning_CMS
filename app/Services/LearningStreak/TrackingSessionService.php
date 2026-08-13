<?php

namespace App\Services\LearningStreak;

use App\Models\CustomerLearningTrackingSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TrackingSessionService
{
    public function start(int $customerId, int $courseId): CustomerLearningTrackingSession
    {
        return DB::transaction(function () use ($customerId, $courseId) {
            CustomerLearningTrackingSession::where('customer_id', $customerId)
                ->whereNull('invalidated_at')
                ->lockForUpdate()
                ->update(['invalidated_at' => now()]);

            $now = CarbonImmutable::now(config('learning_streak.timezone'));
            return CustomerLearningTrackingSession::create([
                'id' => (string) Str::uuid(),
                'customer_id' => $customerId,
                'course_id' => $courseId,
                'started_at' => $now,
                'expires_at' => $now->addHours((int) config('learning_streak.offline_sync_hours')),
            ]);
        });
    }

    public function validate(string $sessionId, int $customerId, int $courseId, string $capturedAt): CustomerLearningTrackingSession
    {
        $session = CustomerLearningTrackingSession::find($sessionId);
        if (!$session || $session->customer_id !== $customerId || $session->course_id !== $courseId) {
            throw new RuntimeException('tracking_session_mismatch');
        }
        $captured = CarbonImmutable::parse($capturedAt)->setTimezone(config('learning_streak.timezone'));
        $now = CarbonImmutable::now(config('learning_streak.timezone'));
        if ($session->invalidated_at && $captured->gt($session->invalidated_at)) {
            throw new RuntimeException('tracking_session_inactive');
        }
        if ($session->expires_at->isPast()) {
            throw new RuntimeException('tracking_session_inactive');
        }
        if ($captured->lt($session->started_at) || $captured->gt($session->expires_at) || $captured->gt($now->addSeconds(30))) {
            throw new RuntimeException('tracking_session_expired');
        }

        return $session;
    }
}
