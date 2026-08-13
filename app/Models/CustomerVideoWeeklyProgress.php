<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerVideoWeeklyProgress extends Model
{
    protected $table = 'customer_video_weekly_progress';

    protected $fillable = [
        'customer_id', 'course_id', 'course_video_id', 'week_start', 'watched_ranges', 'watched_seconds',
    ];

    protected $casts = [
        'week_start' => 'date',
        'watched_ranges' => 'array',
        'watched_seconds' => 'integer',
    ];
}
