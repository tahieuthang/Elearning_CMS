<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
  protected $table = 'reviews';
  protected $fillable = [
    'course_id',
    'customer_id',
    'comment',
    'rate',
  ];
  public $timestamps = true;

  public function customer()
  {
    return $this->belongsTo(Customer::class, 'customer_id');
  }
  
  public function course()
  {
    return $this->belongsTo(Course::class, 'course_id');
  }
}
