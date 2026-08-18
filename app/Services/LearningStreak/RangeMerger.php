<?php

namespace App\Services\LearningStreak;

use InvalidArgumentException;

class RangeMerger
{
    /**
     * @param array<int, array{0:numeric,1:numeric}|array{start:numeric,end:numeric}> $existing
     * @param array<int, array{0:numeric,1:numeric}|array{start:numeric,end:numeric}> $incoming
     * @return array{ranges: array<int, array{0:int,1:int}>, total_seconds:int, new_seconds:int}
     */
    public static function merge(array $existing, array $incoming, int $durationSeconds): array
    {
        if ($durationSeconds <= 0) {
            throw new InvalidArgumentException('Video duration is required for weekly tracking.');
        }

        $current = self::normalize($existing, $durationSeconds, false);
        $before = self::total($current);
        $incoming = self::normalize($incoming, $durationSeconds, true);
        $merged = self::normalize(array_merge($current, $incoming), $durationSeconds, false);
        $total = self::total($merged);

        return [
            'ranges' => $merged,
            'total_seconds' => $total,
            'new_seconds' => $total - $before,
        ];
    }

    /** @return array<int, array{0:int,1:int}> */
    private static function normalize(array $ranges, int $durationSeconds, bool $validateMaximumLength = true): array
    {
        $normalized = array_map(function (array $range) use ($durationSeconds, $validateMaximumLength): array {
            $start = (int) ($range['start'] ?? $range[0] ?? -1);
            $end = (int) ($range['end'] ?? $range[1] ?? -1);
            $maximumRangeLength = ((int) config('learning_streak.heartbeat_seconds') * (int) config('learning_streak.max_playback_rate'))
                + (int) config('learning_streak.range_tolerance_seconds');
            if ($start < 0 || $end <= $start || $end > $durationSeconds || ($validateMaximumLength && ($end - $start) > $maximumRangeLength)) {
                throw new InvalidArgumentException('Invalid watched range.');
            }

            return [$start, $end];
        }, $ranges);

        usort($normalized, fn(array $a, array $b) => $a[0] <=> $b[0]);
        $result = [];
        foreach ($normalized as [$start, $end]) {
            $lastIndex = count($result) - 1;
            if ($lastIndex >= 0 && $start <= $result[$lastIndex][1]) {
                $result[$lastIndex][1] = max($result[$lastIndex][1], $end);
                continue;
            }
            $result[] = [$start, $end];
        }

        return $result;
    }

    /** @param array<int, array{0:int,1:int}> $ranges */
    private static function total(array $ranges): int
    {
        return array_reduce($ranges, fn(int $total, array $range) => $total + ($range[1] - $range[0]), 0);
    }
}
