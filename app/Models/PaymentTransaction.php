<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
  protected $table = 'payment_transactions';
  protected $fillable = [
    'code',
    'order_id',
    'customer_id',
    'amount',
    'payment_method',
    'status',
  ];
  public $timestamps = true;

  public function order()
  {
    $this->belongsTo(Order::class, 'order_id');
  }

  public function customer()
  {
    $this->belongsTo(Customer::class, 'customer_id');
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
