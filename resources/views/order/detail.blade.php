@extends('adminlte::page')

@section('title', __('order.order_management'))

@section('content_header')
<h1>{{ __('order.order_management') }}</h1>
@stop

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">{{ __('order.detail_order') }}: {{ $order->code }}</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form id="customerDetailForm" method="POST" action="" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-body">
            @if ($order)
            <div class="form-group">
              <label for="code">{{ __('order.code') }}</label>
              <input type="text" value="{{ $order->code }}" disabled class="form-control"
                id="code">
            </div>
            <div class="form-group">
              <label for="customer">{{ __('order.order_customer') }}</label>
              <input type="text" value="{{ $order->customer->full_name }}" disabled
                class="form-control" id="customer">
            </div>
            <div class="form-group">
              <label for="payment_method">{{ __('order.payment_method') }}</label>
              <input type="text" value="{{ $order->payment_method }}" disabled class="form-control"
                id="payment_method">
            </div>
            <div class="form-group">
              <label for="payment_time">{{ __('order.payment_time') }}</label>
              <input type="text" value="{{ $order->payment_time }}" disabled class="form-control"
                id="payment_time">
            </div>
            <div class="form-group">
              <label for="status">{{ __('order.amount') }}</label>
              <input type="text" value="{{ \App\Helpers\Helper::convertMoney($order->amount) }}" disabled class="form-control"
                id="status">
            </div>
            <div class="form-group">
              <label for="status">{{ __('order.status') }}</label>
              <input type="text" value="{{ __('order.status_list_by_value')[$order->status] }}"
                disabled class="form-control" id="status">
            </div>
            <div class="form-group">
              <label for="created_at">{{ __('order.created_at') }}</label>
              <input type="text" value="{{ $order->created_at }}" disabled class="form-control"
                id="created_at">
            </div>
            <div class="form-group">
              <label for="items_list">{{ __('order.items_list.title') }}</label>
            </div>
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">{{ __('order.items_list.course_title') }}</th>
                  <th scope="col">{{ __('order.items_list.quantity') }}</th>
                  <th scope="col">{{ __('order.items_list.price') }}</th>
                  <th scope="col">{{ __('order.items_list.amount') }}</th>
                </tr>
              </thead>
              <tbody>
                @if(count($order->orderItems))
                @foreach ($order->orderItems as $key => $item)
                <tr>
                  <th scope="row">{{ $key + 1 }}</th>
                  <td>{{ $item->course_title }}</td>
                  <td>{{ $item->quantity }}</td>
                  <td>{{ \App\Helpers\Helper::convertMoney($item->price) }}</td>
                  <td>{{ \App\Helpers\Helper::convertMoney($item->price * $item->quantity) }}</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@include('common.loadingSpinner')
@stop

@section('js')
@stop