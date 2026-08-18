<?php

return [
    'timezone' => env('LEARNING_STREAK_TIMEZONE', 'Asia/Ho_Chi_Minh'),
    'watch_target_seconds' => 1800,
    'heartbeat_seconds' => 10,
    'max_playback_rate' => 2,
    'range_tolerance_seconds' => 3,
    'max_ranges_per_request' => 20,
    'offline_sync_hours' => 24,
];
