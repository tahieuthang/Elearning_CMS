<?php

namespace Tests\Unit\DemoCourses;

use App\Services\DemoCourseSeederService;
use PHPUnit\Framework\TestCase;

class SeedDemoCoursesCommandTest extends TestCase
{
    public function test_it_exposes_an_explicit_seed_command_with_a_bounded_count_option(): void
    {
        $commandClass = 'App\\Console\\Commands\\SeedDemoCourses';

        $this->assertTrue(class_exists($commandClass));

        $command = new $commandClass(new DemoCourseSeederService);

        $this->assertSame('demo:seed-courses', $command->getName());
        $this->assertTrue($command->getDefinition()->hasOption('count'));
    }
}
