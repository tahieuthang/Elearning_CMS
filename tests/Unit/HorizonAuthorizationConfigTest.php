<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HorizonAuthorizationConfigTest extends TestCase
{
    public function test_horizon_dashboard_uses_the_existing_permission_guard(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/app/Providers/HorizonServiceProvider.php');
        $providers = file_get_contents(dirname(__DIR__, 2).'/bootstrap/providers.php');
        $permissions = file_get_contents(dirname(__DIR__, 2).'/config/constants.php');

        $this->assertIsString($provider);
        $this->assertIsString($providers);
        $this->assertIsString($permissions);
        $this->assertStringContainsString("Gate::define('viewHorizon'", $provider);
        $this->assertStringContainsString("hasPermissionTo('horizon.view')", $provider);
        $this->assertStringContainsString('App\\Providers\\HorizonServiceProvider::class', $providers);
        $this->assertStringContainsString("'horizon.view'", $permissions);
    }
}
