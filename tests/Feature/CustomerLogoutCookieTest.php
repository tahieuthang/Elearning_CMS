<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerLogoutCookieTest extends TestCase
{
    public function test_logout_expires_refresh_cookie_at_the_same_path_used_when_issuing_it(): void
    {
        $response = $this->postJson('/api/customer/logout');
        $refreshCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === config('jwt.refresh_cookie'));

        $response
            ->assertOk()
            ->assertCookieExpired(config('jwt.refresh_cookie'));

        $this->assertNotNull($refreshCookie);
        $this->assertSame('/api/customer', $refreshCookie->getPath());
    }
}
