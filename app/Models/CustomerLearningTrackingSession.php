<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CustomerLearningTrackingSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'id', 'customer_id', 'course_id', 'started_at', 'expires_at', 'invalidated_at', 'last_seen_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'invalidated_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];
}
