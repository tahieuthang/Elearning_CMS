<?php

namespace App\Services\LearningStreak;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class LearningCourseAccess
{
    public function canAccess(int $customerId, Course $course): bool
    {
        if ((int) $course->status !== 1) {
            return false;
        }

        if ((float) $course->original_price === 0.0 || (float) $course->sale_off_price === 0.0) {
            return true;
        }

        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.customer_id', $customerId)
            ->where('orders.status', 3)
            ->where('order_items.course_id', $course->id)
            ->exists();
    }
}
