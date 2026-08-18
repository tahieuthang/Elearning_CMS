<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentTransactionServices;

class PaymentTransactionController extends Controller
{
  private $paymentTransactionServices;

  public function __construct(PaymentTransactionServices $paymentTransactionServices)
  {
    $this->paymentTransactionServices = $paymentTransactionServices;
  }

  public function list(Request $request)
  {
    $paymentTransactionStatus = \Config::get('constants.payment_transaction_status');
    return view('payment_transaction.index', [
      'paymentTransactionStatus' => $paymentTransactionStatus,
    ]);
  }

  public function anyData(Request $request)
  {
    $filterData = [];
    if (isset($request->order_code) || isset($request->statusList)) {
      if ($request->order_code) {
        $filterData['order_code'] = $request->order_code;
      }
      if ($request->statusList) {
        $filterData['statusList'] = $request->statusList;
      }
    }
    $data = $this->paymentTransactionServices->getPaymentTransactions($filterData);
    $datatableFormat = $this->paymentTransactionServices->formatPaymentTransactionsDatatables($data);
    return $datatableFormat;
  }
}
