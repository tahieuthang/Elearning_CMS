<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OrderServices;
use App\Models\Customer;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
  private $orderServices;

  public function __construct(OrderServices $orderServices)
  {
    $this->orderServices = $orderServices;
  }

  public function statisticsPage(Request $request)
  {
    $totalCustomer = Customer::where('status', Config::get('constants.customer_status_enable'))->count();
    $totalCourse = Course::where('status', Config::get('constants.course_status_by_text.active'))->count();
    $totalOrder = Order::where('status', Config::get('constants.order_status.completed'))->count();
    $revenue = Order::where('status', Config::get('constants.order_status.completed'))->sum('amount');
    // dd($totalCustomer, $totalCourse, $totalOrder, $revenue);
    $monthlyRevenue = array_fill(0, 12, 0);
    $revenueData = DB::table('orders')
      ->where('status', Config::get('constants.order_status.completed'))
      ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(amount) as total'))
      ->groupBy(DB::raw('MONTH(created_at)'))
      ->orderBy('month', 'asc')
      ->get();

    foreach ($revenueData as $data) {
      $monthlyRevenue[$data->month - 1] = $data->total / 1000000;
    }
    // $monthlyRevenue = array_values($monthlyRevenue);
    // dd($monthlyRevenue);
    $topPurchasedCourses = Course::withCount('items')->get();
    // dd($topPurchasedCourses);
    return view('home', [
      'totalCustomer' => $totalCustomer,
      'totalCourse' => $totalCourse,
      'totalOrder' => $totalOrder,
      'revenue' => $revenue,
      'monthlyRevenue' => $monthlyRevenue,
      'topPurchasedCourses' => $topPurchasedCourses,
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
