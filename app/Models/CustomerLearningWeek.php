<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLearningWeek extends Model
{
    protected $fillable = [
        'customer_id', 'week_start', 'visit_completed_at', 'watched_seconds',
        'watch_completed_at', 'qualified_at',
    ];

    protected $casts = [
        'week_start' => 'date',
        'visit_completed_at' => 'datetime',
        'watch_completed_at' => 'datetime',
        'qualified_at' => 'datetime',
        'watched_seconds' => 'integer',
    ];
}
