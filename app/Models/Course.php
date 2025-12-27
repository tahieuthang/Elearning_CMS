<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Course extends Model
{
  use SoftDeletes;

  protected $dates = ['deleted_at'];
  protected $table = 'courses';

  public $timestamps = true;

  public function courseTags()
  {
    return $this->belongsToMany(Tag::class, 'course_tags');
  }
  public function courseCategories()
  {
    return $this->belongsToMany(PostCategory::class, 'course_category_pivot');
  }
  public function videos()
  {
    return $this->hasMany(CourseVideo::class);
  }
  public function orders()
  {
    return $this->belongsToMany(Order::class, 'order_items', 'course_id', 'order_id');
  }
  public function items()
  {
    return $this->hasMany(OrderItem::class);
  }
  public function reviews()
  {
    return $this->hasMany(Review::class);
  }
  public static function getCourseRelationShipById($id)
  {
    $course = Course::with('courseCategories', 'courseTags', 'videos')->find($id);
    return $course;
  }
  
  /**
   * Update rating average and count for this course
   */
  public function updateRating()
  {
    $ratingStats = $this->reviews()
      ->selectRaw('AVG(rate) as average, COUNT(*) as count')
      ->first();
    // Only attempt to update if the columns exist in DB (migration may not have run)
    $updates = [];
    if (Schema::hasColumn($this->getTable(), 'rating_average')) {
      $updates['rating_average'] = round($ratingStats->average ?? 0, 2);
    }
    if (Schema::hasColumn($this->getTable(), 'rating_count')) {
      $updates['rating_count'] = $ratingStats->count ?? 0;
    }

    if (!empty($updates)) {
      $this->update($updates);
    }
  }
}
