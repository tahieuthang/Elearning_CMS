<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderServices;
use Illuminate\Support\Facades\Config;

class OrderController extends Controller
{
  private $orderServices;

  public function __construct(OrderServices $orderServices)
  {
    $this->orderServices = $orderServices;
  }

  public function list(Request $request)
  {
    $orderStatus = Config::get('constants.order_status');
    return view('order.index', [
      'orderStatus' => $orderStatus,
    ]);
  }

  public function anyData(Request $request)
  {
    $filterData = [];
    if (isset($request->code) || isset($request->statusList)) {
      if ($request->code) {
        $filterData['code'] = $request->code;
      }
      if ($request->statusList) {
        $filterData['statusList'] = $request->statusList;
      }
    }
    $data = $this->orderServices->getOrders($filterData);
    $datatableFormat = $this->orderServices->formatOrdersDatatables($data);
    return $datatableFormat;
  }

  public function detail($id)
  {
    $order = $this->orderServices->getDetailOrder($id);
    if ($order) {
      return view('order.detail', ['order' => $order]);
    }
    return abort(404);
  }
}
