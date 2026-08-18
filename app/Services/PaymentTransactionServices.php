<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Yajra\Datatables\Datatables;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Config;

class PaymentTransactionServices
{
  public function __construct() {}

  public function getPaymentTransactions($filterData)
  {
    $queries = PaymentTransaction::with(["order", "customer"]);
    if (isset($filterData["order_code"]) && $filterData["order_code"]) {
      $queries->whereHas('order', function ($q) use ($filterData) {
        $likeStr = "%" . Helper::escapeLike($filterData["order_code"]) . "%";
        return $q->where("orders.code", "like", $likeStr);
      });
    }
    if (isset($filterData["statusList"]) && count($filterData["statusList"]) > 0) {
      $queries->whereIn("status", $filterData["statusList"]);
    }
    return $queries;
  }

  public function formatPaymentTransactionsDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn("status", function ($row) {
        $statusTxt = __("payment_transaction.status_list_by_value")[$row->status];
        if ($row->status === Config::get('constants.payment_transaction_status.waiting_confirm')) {
          return '<span class="badge bg-info mr-1">' . $statusTxt . '</span>';
        }
        if ($row->status === Config::get('constants.payment_transaction_status.completed')) {
          return '<span class="badge bg-success mr-1">' . $statusTxt . '</span>';
        }
        if ($row->status === Config::get('constants.payment_transaction_status.failed')) {
          return '<span class="badge bg-danger mr-1">' . $statusTxt . '</span>';
        }
      })
      ->addColumn("orderCode", function ($row) {
        if ($row->order) {
          return $row->order->code;
        }
        return "";
      })
      ->addColumn("customer", function ($row) {
        if ($row->customer) {
          return $row->customer->full_name;
        }
        return "";
      })
      ->addColumn("amount", function ($row) {
        return Helper::convertMoney($row->amount);
      })
      ->rawColumns([
        "status",
        "orderCode",
        "customer",
      ])
      ->make(true);
  }
}
