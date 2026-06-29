<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerQuiz extends Model
{
    protected $table = 'customer_quizzes';

    protected $fillable = [
        'customer_id',
        'quiz_id',
        'is_passed',
    ];

    protected $casts = [
        'is_passed' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
