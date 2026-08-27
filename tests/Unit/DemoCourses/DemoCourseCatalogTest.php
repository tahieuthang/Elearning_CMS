<?php

namespace Tests\Unit\DemoCourses;

use PHPUnit\Framework\TestCase;

class DemoCourseCatalogTest extends TestCase
{
    public function test_it_exposes_thirty_complete_unique_demo_course_records(): void
    {
        $catalogClass = 'App\\Support\\DemoCourses\\DemoCourseCatalog';

        $this->assertTrue(class_exists($catalogClass));

        $courses = $catalogClass::all();

        $this->assertCount(30, $courses);
        $this->assertCount(30, array_unique(array_column($courses, 'key')));

        foreach ($courses as $course) {
            $this->assertArrayHasKey('key', $course);
            $this->assertArrayHasKey('title', $course);
            $this->assertArrayHasKey('description', $course);
            $this->assertArrayHasKey('author', $course);
            $this->assertArrayHasKey('original_price', $course);
            $this->assertArrayHasKey('sale_off_price', $course);
            $this->assertMatchesRegularExpression('/^https:\/\/images\.unsplash\.com\//', $course['thumbnail']);
            $this->assertNotEmpty($course['tags']);
            $this->assertNotEmpty($course['topics']);
        }
    }
}
