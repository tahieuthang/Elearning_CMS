<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerQuizProgress extends Model
{
    protected $table = 'customer_quiz_progress';

    protected $fillable = [
        'customer_id',
        'course_id',
        'quiz_id',
        'is_completed',
    ];

    public $timestamps = true;

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}
