<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerCourseProgress extends Model
{
    protected $table = 'customer_course_progress';

    protected $fillable = [
        'customer_id',
        'course_id',
        'progress_percent',
    ];

    public $timestamps = true;

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
