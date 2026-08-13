<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseVideo extends Model
{
  protected $table = 'course_videos';
  protected $fillable = [
    'course_id',
    'video_title',
    'video_description',
    'vimeo_id',
    'video_thumbnail',
    'duration_seconds',
    'order',
    'created_at',
    'updated_at',
  ];
  public $timestamps = true;
  protected $casts = [
    'duration_seconds' => 'integer',
  ];
}
