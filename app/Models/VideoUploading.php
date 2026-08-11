<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoUploading extends Model
{
  protected $table = 'video_uploadings';
  protected $fillable = [
    'video_id',
    'vimeo_id',
    'file_path',
    'thumbnail_id',
    'job_id',
    'job_status',
    'error_log',
    'created_at',
    'updated_at',
  ];

  public $timestamps = true;
}
