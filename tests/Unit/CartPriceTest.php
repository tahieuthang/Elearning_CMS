<?php

namespace Tests\Unit;

use App\Services\CartServices;
use PHPUnit\Framework\TestCase;

class CartPriceTest extends TestCase
{
    public function test_zero_sale_price_is_the_current_cart_price(): void
    {
        $course = (object) [
            'original_price' => 299999,
            'sale_off_price' => 0,
        ];

        $this->assertSame(0, CartServices::resolveCurrentPrice($course));
    }

    public function test_null_sale_price_falls_back_to_original_price(): void
    {
        $course = (object) [
            'original_price' => 299999,
            'sale_off_price' => null,
        ];

        $this->assertSame(299999, CartServices::resolveCurrentPrice($course));
    }
}
