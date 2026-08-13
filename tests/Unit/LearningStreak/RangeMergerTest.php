<?php

namespace Tests\Unit\LearningStreak;

use App\Services\LearningStreak\RangeMerger;
use InvalidArgumentException;
use Tests\TestCase;

class RangeMergerTest extends TestCase
{
    public function test_it_merges_overlapping_and_adjacent_ranges(): void
    {
        $result = RangeMerger::merge([], [[0, 20], [10, 30], [30, 40]], 120);

        $this->assertSame([[0, 40]], $result['ranges']);
        $this->assertSame(40, $result['total_seconds']);
        $this->assertSame(40, $result['new_seconds']);
    }

    public function test_a_retry_does_not_add_seconds_twice(): void
    {
        $result = RangeMerger::merge([[0, 20]], [[0, 20]], 120);

        $this->assertSame([[0, 20]], $result['ranges']);
        $this->assertSame(0, $result['new_seconds']);
    }

    public function test_it_does_not_count_the_gap_created_by_a_seek(): void
    {
        $result = RangeMerger::merge([], [[0, 20], [80, 100]], 120);

        $this->assertSame([[0, 20], [80, 100]], $result['ranges']);
        $this->assertSame(40, $result['total_seconds']);
    }

    public function test_it_allows_an_existing_merged_range_to_grow_on_the_next_heartbeat(): void
    {
        $result = RangeMerger::merge([[0, 30]], [[30, 40]], 120);

        $this->assertSame([[0, 40]], $result['ranges']);
        $this->assertSame(10, $result['new_seconds']);
    }

    public function test_it_rejects_an_invalid_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RangeMerger::merge([], [[20, 20]], 120);
    }

    public function test_it_rejects_a_range_longer_than_one_heartbeat_allows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RangeMerger::merge([], [[0, 24]], 120);
    }
}
