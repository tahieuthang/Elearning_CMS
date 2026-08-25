<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HorizonInfrastructureConfigTest extends TestCase
{
    public function test_production_horizon_configuration_matches_long_running_queue_jobs(): void
    {
        $horizon = file_get_contents(dirname(__DIR__, 2).'/config/horizon.php');
        $queue = file_get_contents(dirname(__DIR__, 2).'/config/queue.php');
        $environment = file_get_contents(dirname(__DIR__, 2).'/.env.example');

        $this->assertIsString($horizon);
        $this->assertStringContainsString("'production' => [", $horizon);
        $this->assertStringContainsString("'connection' => 'redis'", $horizon);
        $this->assertStringContainsString("'queue' => ['default']", $horizon);
        $this->assertStringContainsString("'maxProcesses' => 1", $horizon);
        $this->assertStringContainsString("'timeout' => 600", $horizon);
        $this->assertStringContainsString("env('REDIS_QUEUE_RETRY_AFTER', 660)", $queue);
        $this->assertStringContainsString('REDIS_QUEUE_RETRY_AFTER=660', $environment);
    }

    public function test_production_compose_uses_horizon_and_scheduler_without_publishing_ports(): void
    {
        $compose = file_get_contents(dirname(__DIR__, 2).'/docker-compose.yml');

        $this->assertIsString($compose);
        $this->assertStringContainsString("  horizon:\n", $compose);
        $this->assertStringContainsString('command: php artisan horizon', $compose);
        $this->assertStringContainsString("  scheduler:\n", $compose);
        $this->assertStringContainsString('command: php artisan schedule:work', $compose);
        $this->assertStringNotContainsString("  queue:\n", $compose);

        foreach (['horizon', 'scheduler'] as $service) {
            preg_match('/\\n  '.$service.':\\n(?<block>.*?)(?=\\n  [a-z-]+:|\\z)/s', $compose, $matches);

            $this->assertArrayHasKey('block', $matches);
            $this->assertStringNotContainsString('ports:', $matches['block']);
        }
    }

    public function test_horizon_snapshot_is_scheduled_every_five_minutes(): void
    {
        $consoleRoutes = file_get_contents(dirname(__DIR__, 2).'/routes/console.php');

        $this->assertIsString($consoleRoutes);
        $this->assertStringContainsString("Schedule::command('horizon:snapshot')->everyFiveMinutes();", $consoleRoutes);
    }

    public function test_development_compose_uses_a_local_horizon_supervisor_and_scheduler(): void
    {
        $horizon = file_get_contents(dirname(__DIR__, 2).'/config/horizon.php');
        $compose = file_get_contents(dirname(__DIR__, 2).'/docker-compose.dev.yml');

        $this->assertIsString($horizon);
        $this->assertIsString($compose);
        $this->assertStringContainsString("'local' => [", $horizon);
        $this->assertStringContainsString("'local-supervisor' => [", $horizon);
        $this->assertStringContainsString("  horizon:\n", $compose);
        $this->assertStringContainsString('command: php artisan horizon', $compose);
        $this->assertStringContainsString("  scheduler:\n", $compose);
        $this->assertStringContainsString('command: php artisan schedule:work', $compose);
        $this->assertStringNotContainsString("  queue:\n", $compose);
    }
}
