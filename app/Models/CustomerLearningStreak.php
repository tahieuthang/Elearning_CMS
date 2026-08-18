<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLearningStreak extends Model
{
    protected $primaryKey = 'customer_id';
    public $incrementing = false;

    protected $fillable = ['customer_id', 'current_streak', 'longest_streak', 'last_qualified_week'];

    protected $casts = [
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'last_qualified_week' => 'date',
    ];
}
