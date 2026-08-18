<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ReverbInfrastructureConfigTest extends TestCase
{
    public function test_production_compose_runs_reverb_without_publishing_its_port(): void
    {
        $compose = file_get_contents(dirname(__DIR__, 2).'/docker-compose.yml');

        $this->assertIsString($compose);
        preg_match('/\n  reverb:\n(?<block>.*?)(?=\n  [a-z-]+:|\z)/s', $compose, $matches);

        $this->assertArrayHasKey('block', $matches);
        $this->assertStringContainsString('php artisan reverb:start', $matches['block']);
        $this->assertStringNotContainsString('ports:', $matches['block']);
    }

    public function test_development_compose_defines_reverb_service(): void
    {
        $compose = file_get_contents(dirname(__DIR__, 2).'/docker-compose.dev.yml');

        $this->assertIsString($compose);
        $this->assertStringContainsString("  reverb:\n", $compose);
        $this->assertStringContainsString('php artisan reverb:start', $compose);
    }

    public function test_nginx_configs_proxy_reverb_upgrade_requests(): void
    {
        foreach (['nginx.conf', 'nginx.dev.conf'] as $file) {
            $config = file_get_contents(dirname(__DIR__, 2).'/docker-compose/nginx/'.$file);

            $this->assertIsString($config);
            $this->assertStringContainsString('location ^~ /app', $config);
            $this->assertStringContainsString('proxy_pass http://reverb:8080;', $config);
            $this->assertStringContainsString('proxy_http_version 1.1;', $config);
            $this->assertStringContainsString('proxy_set_header Upgrade $http_upgrade;', $config);
            $this->assertStringContainsString('proxy_set_header Connection "upgrade";', $config);
        }
    }
}
