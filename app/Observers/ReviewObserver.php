<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\Course;

class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $this->updateCourseRating($review->course_id);
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        // Update rating if rate changed
        if ($review->isDirty('rate')) {
            $this->updateCourseRating($review->course_id);
        }
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        $this->updateCourseRating($review->course_id);
    }

    /**
     * Update rating for a course
     */
    private function updateCourseRating($courseId): void
    {
        $course = Course::find($courseId);
        if ($course) {
            $course->updateRating();
        }
    }
}

