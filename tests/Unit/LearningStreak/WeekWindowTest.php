<?php

namespace Tests\Unit\LearningStreak;

use App\Services\LearningStreak\WeekWindow;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class WeekWindowTest extends TestCase
{
    public function test_it_resolves_a_vietnam_week_from_a_midweek_timestamp(): void
    {
        $window = WeekWindow::from(CarbonImmutable::parse('2026-08-12 10:00:00', 'Asia/Ho_Chi_Minh'));

        $this->assertSame('2026-08-10', $window->startDate->toDateString());
        $this->assertSame('2026-08-16', $window->endDate->toDateString());
    }

    public function test_it_converts_an_input_timestamp_before_calculating_the_week(): void
    {
        $window = WeekWindow::from(CarbonImmutable::parse('2026-08-16 18:00:00', 'UTC'));

        $this->assertSame('2026-08-17', $window->startDate->toDateString());
    }

    public function test_monday_starts_a_new_week(): void
    {
        $window = WeekWindow::from(CarbonImmutable::parse('2026-08-17 00:00:00', 'Asia/Ho_Chi_Minh'));

        $this->assertSame('2026-08-17', $window->startDate->toDateString());
    }
}
