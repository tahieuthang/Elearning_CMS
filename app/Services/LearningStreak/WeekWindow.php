<?php

namespace App\Services\LearningStreak;

use Carbon\CarbonImmutable;
use DateTimeInterface;

class WeekWindow
{
    public function __construct(
        public readonly CarbonImmutable $startDate,
        public readonly CarbonImmutable $endDate,
    ) {
    }

    public static function from(DateTimeInterface|string|null $dateTime = null): self
    {
        $timezone = config('learning_streak.timezone', 'Asia/Ho_Chi_Minh');
        $moment = $dateTime instanceof DateTimeInterface
            ? CarbonImmutable::instance($dateTime)->setTimezone($timezone)
            : CarbonImmutable::parse($dateTime ?? 'now', $timezone);
        $start = $moment->startOfDay()->subDays($moment->dayOfWeekIso - 1);

        return new self($start, $start->addDays(6));
    }
}
