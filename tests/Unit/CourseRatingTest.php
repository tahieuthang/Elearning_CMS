<?php

namespace Tests\Unit;

use App\Models\Course;
use Mockery;
use PHPUnit\Framework\TestCase;

class CourseRatingTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_rating_aggregate_is_saved_as_server_computed_fields(): void
    {
        $ratingStats = (object) [
            'average' => 4.5,
            'count' => 2,
        ];
        $reviews = Mockery::mock();
        $course = Mockery::mock(Course::class)->makePartial();

        $course->shouldReceive('reviews')
            ->once()
            ->andReturn($reviews);
        $reviews->shouldReceive('selectRaw')
            ->once()
            ->with('AVG(rate) as average, COUNT(*) as count')
            ->andReturnSelf();
        $reviews->shouldReceive('first')
            ->once()
            ->andReturn($ratingStats);
        $course->shouldReceive('update')
            ->never();
        $course->shouldReceive('forceFill')
            ->once()
            ->with([
                'rating_average' => 4.5,
                'rating_count' => 2,
            ])
            ->andReturnSelf();
        $course->shouldReceive('save')
            ->once()
            ->andReturnTrue();

        $course->updateRating();

        $this->addToAssertionCount(1);
    }
}
