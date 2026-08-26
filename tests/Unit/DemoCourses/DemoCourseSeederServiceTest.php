<?php

namespace Tests\Unit\DemoCourses;

use Illuminate\Support\Collection;
use LogicException;
use PHPUnit\Framework\TestCase;

class DemoCourseSeederServiceTest extends TestCase
{
    public function test_it_assigns_courses_to_categories_round_robin(): void
    {
        $serviceClass = 'App\\Services\\DemoCourseSeederService';

        $this->assertTrue(class_exists($serviceClass));

        $service = new $serviceClass;
        $assignments = $service->assignCoursesToCategories(
            [['key' => 'a'], ['key' => 'b'], ['key' => 'c'], ['key' => 'd'], ['key' => 'e']],
            new Collection([(object) ['id' => 11], (object) ['id' => 22]]),
        );

        $this->assertSame([11, 22, 11, 22, 11], array_column($assignments, 'category_id'));
    }

    public function test_it_prioritizes_courses_that_match_the_category_topic(): void
    {
        $service = new \App\Services\DemoCourseSeederService;

        $assignments = $service->assignCoursesToCategories(
            [
                ['key' => 'node', 'topics' => ['development']],
                ['key' => 'yoga', 'topics' => ['health-fitness']],
                ['key' => 'nutrition', 'topics' => ['health-fitness']],
                ['key' => 'react', 'topics' => ['development']],
            ],
            new Collection([
                (object) ['id' => 11, 'category_name' => 'Development'],
                (object) ['id' => 22, 'category_name' => 'Health & Fitness'],
            ]),
        );

        $categoryByCourse = [];
        foreach ($assignments as $assignment) {
            $categoryByCourse[$assignment['course']['key']] = $assignment['category_id'];
        }

        $this->assertSame([
            'node' => 11,
            'react' => 11,
            'yoga' => 22,
            'nutrition' => 22,
        ], $categoryByCourse);
    }

    public function test_it_refuses_to_seed_courses_when_no_categories_exist(): void
    {
        $serviceClass = 'App\\Services\\DemoCourseSeederService';

        $this->assertTrue(class_exists($serviceClass));

        $this->expectException(LogicException::class);

        (new $serviceClass)->assignCoursesToCategories([['key' => 'a']], new Collection);
    }
}
