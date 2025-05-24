<?php

namespace App\Services;

use App\Helpers\Helper as HelpersHelper;
use App\Models\Order;
use Yajra\Datatables\Datatables;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Config;

class OrderServices
{
  public function getOrders($filterData)
  {
    $queries = Order::with('orderItems', 'courses');
    if (isset($filterData['code']) && $filterData['code']) {
      $queries->where(function ($q) use ($filterData) {
        $likeStr = "%" . Helper::escapeLike($filterData["code"]) . "%";
        $q->where('orders.code', 'like', $likeStr);
      });
    }
    if (isset($filterData['statusList']) && count($filterData['statusList']) > 0) {
      $queries->where(function ($q) use ($filterData) {
        $q->whereIn('status', $filterData['statusList']);
      });
    }
    return $queries;
  }
  public function formatOrdersDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn("status", function ($row) {
        $statusTxt = __("order.status_list_by_value")[$row->status];
        if ($row->status === Config::get('constants.order_status.placed')) {
          return '<span class="badge bg-primary mr-1">' . $statusTxt . '</span>';
        }
        if ($row->status === Config::get('constants.order_status.processing')) {
          return '<span class="badge bg-info mr-1">' . $statusTxt . '</span>';
        }
        if ($row->status === Config::get('constants.order_status.completed')) {
          return '<span class="badge bg-success mr-1">' . $statusTxt . '</span>';
        }
        if ($row->status === Config::get('constants.order_status.cancelled')) {
          return '<span class="badge bg-danger mr-1">' . $statusTxt . '</span>';
        }
      })
      ->addColumn("orderCustomer", function ($row) {
        if ($row->customer) {
          return $row->customer->full_name;
        }
        return "";
      })
      ->addColumn("amount", function ($row) {
        return Helper::convertMoney($row->amount);
      })
      ->addColumn("action", function ($row) {
        $action = "";
        if (Helper::checkPermission("order.detail")) {
          $action .=
            '<a href="/order/detail/' . $row->id . '" class="edit btn btn-primary btn-sm mr-1">' . __("order.detail_order") . "</a>";
        }
        return $action;
      })
      ->rawColumns(["status", "orderCustomer", "action",])
      ->make(true);
  }

  public function getDetailOrder($id)
  {
    $order = Order::find($id);
    return $order;
  }
}
