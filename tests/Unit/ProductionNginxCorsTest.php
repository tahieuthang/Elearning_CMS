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

    public function test_production_nginx_rate_limits_api_requests_by_ip(): void
    {
        $config = file_get_contents(dirname(__DIR__, 2).'/docker-compose/nginx/nginx.conf');

        $this->assertIsString($config);
        $this->assertStringContainsString(
            'limit_req_zone $binary_remote_addr zone=api_general:10m rate=10r/s;',
            $config
        );
        $this->assertStringContainsString(
            'limit_req_zone $binary_remote_addr zone=api_register:10m rate=2r/s;',
            $config
        );
        $this->assertStringContainsString('location ^~ /api/', $config);
        $this->assertStringContainsString('limit_req zone=api_general burst=20 nodelay;', $config);
        $this->assertStringContainsString('location = /api/customer/register', $config);
        $this->assertStringContainsString('limit_req zone=api_register burst=5 nodelay;', $config);
        $this->assertStringContainsString('limit_req_status 429;', $config);
    }
}
