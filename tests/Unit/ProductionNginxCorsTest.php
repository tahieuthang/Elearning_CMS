<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ProductionNginxCorsTest extends TestCase
{
    public function test_production_nginx_defers_credentialed_cors_to_laravel(): void
    {
        $config = file_get_contents(dirname(__DIR__, 2).'/docker-compose/nginx/nginx.conf');

        $this->assertIsString($config);
        $this->assertStringNotContainsString('Access-Control-Allow-Origin', $config);
        $this->assertStringNotContainsString('Access-Control-Allow-Methods', $config);
        $this->assertStringNotContainsString('Access-Control-Allow-Headers', $config);
    }
}
