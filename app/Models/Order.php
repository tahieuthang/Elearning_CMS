<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Illuminate\Support\Str;

class Order extends Model
{
  protected $table = 'orders';
  protected $fillable = [
    'code',
    'amount',
    'customer_id',
    'payment_method',
    'payment_time',
    'status',
    'coupon_code',
    'discount_amount',
  ];

  public $timestamps = true;

  protected $casts = [
    'amount' => 'integer',
    'payment_time' => 'datetime',
    'status' => 'integer',
    'discount_amount' => 'integer',
  ];

  public function orderItems()
  {
    return $this->hasMany(OrderItem::class, 'order_id');
  }

  public function customer()
  {
    return $this->belongsTo(Customer::class, 'customer_id');
  }

  public function courses()
  {
    return $this->belongsToMany(Course::class, 'order_items', 'order_id', 'course_id');
  }

  public function paymentTransaction()
  {
    return $this->hasOne(PaymentTransaction::class, 'order_id');
  }

  public static function generateCode($prefix, $index = null, $field = null)
  {
      $tableName = (new self)->getTable(); // tự lấy tên bảng của model
      $field = $field ?? 'code';

      // Lấy auto_increment hiện tại (cẩn thận với MariaDB)
      $statement = DB::select("SHOW TABLE STATUS LIKE '$tableName'");
      $index = $index ?? ($statement[0]->Auto_increment ?? 1);

      // Tạo code mới
      $code = self::generateCodeSimple($prefix, $index);

      // Nếu code đã tồn tại thì đệ quy gọi lại với index + 1
      if (self::codeExists($code, $field)) {
          return self::generateCode($prefix, $index + 1, $field);
      }

      return $code;
  }

  public static function generateCodeSimple($prefix, $index)
  {
      $random = strtoupper(Str::random(5));
      $date = date('YmdHis');
      return $prefix . $date . $random . $index;
  }

  public static function codeExists($code, $field = null)
  {
      return self::where($field, $code)->exists(); // đúng model hiện tại
  }
}
