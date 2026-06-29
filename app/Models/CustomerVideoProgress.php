<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerVideoProgress extends Model
{
    protected $table = 'customer_video_progress';

    protected $fillable = [
        'customer_id',
        'course_id',
        'course_video_id',
        'watched_seconds',
        'total_seconds',
        'is_completed',
    ];

    public $timestamps = true;

    public function courseVideo()
    {
        return $this->belongsTo(CourseVideo::class, 'course_video_id');
    }
}
