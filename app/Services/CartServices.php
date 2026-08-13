<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tag;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Course;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartServices
{
  public static function resolveCurrentPrice($course)
  {
    return $course->sale_off_price !== null
      ? $course->sale_off_price
      : $course->original_price;
  }

  public function getCartContent()
  {
    return Cart::select(
      'carts.id',
      'carts.customer_id',
      'carts.course_id',
      'carts.course_title',
      'carts.quantity',
      DB::raw('CASE WHEN courses.sale_off_price IS NULL THEN courses.original_price ELSE courses.sale_off_price END AS price'),
      'carts.created_at',
      'carts.updated_at'
    )
      ->with(['course'])
      ->join('courses', 'courses.id', '=', 'carts.course_id')
      ->where('carts.customer_id', auth('customer')->user()->id)
      ->get();
  }

  public function addCartItem($data)
  {
    $cource = Course::find($data['course_id']);
    if (!$cource || !$data['quantity']) {
      return true;
    }
    $existCourseInCart = Cart::where('customer_id', auth('customer')->user()->id)
      ->where('course_id', $data['course_id'])
      ->first();
    if ($existCourseInCart) {
      // UPDATE +QUANTITY
      return $existCourseInCart->update([
        'quantity' => $existCourseInCart->quantity + $data['quantity']
      ]);
    } else {
      // CREATE NEW
      return Cart::create([
        'customer_id' => auth('customer')->user()->id,
        'course_id' => $data['course_id'],
        'course_title' => $cource->title,
        'quantity' => $data['quantity'],
        'price' => self::resolveCurrentPrice($cource),
      ]);
    }
  }

  public function updateCartItem($id, $data)
  {

    $cartExist = Cart::where([
      'id' => $id,
      'customer_id' => auth('customer')->user()->id,
    ])->first();
    if ($cartExist) {
      return $cartExist->update([
        'quantity' => $data['quantity']
      ]);
    } else {
      throw new NotFoundHttpException(__('message.not_exist_cart_item'));
    };
  }

  public function deleteCartItem($id)
  {
    $cart = Cart::where('customer_id', auth('customer')->user()->id)
      ->where('id', $id)
      ->first();
    if ($cart) {
      return $cart->delete();
    } else {
      throw new NotFoundHttpException(__('message.not_exist_cart_item'));
    }
  }

  public function destroyCart()
  {
    return Cart::where('customer_id', auth('customer')->user()->id)->delete();
  }
}
