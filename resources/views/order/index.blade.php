@extends('adminlte::page')

@section('title', __('order.order_management'))

@section('content_header')
<h1>{{ __('order.order_management') }}</h1>
@stop

@section('css')
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-6 form-group">
            <label>{{ __('order.filter_code') }}</label>
            <div class="select2-purple">
              <input class="form-control" type="text" name="order_code" id="order_code"
                placeholder="{{ __('order.filter_code_placeholder') }}" />
            </div>
          </div>
          <div class="col-6 form-group">
            <label>{{ __('order.filter_status') }}</label>
            <div class="select2-purple">
              <select id="status-filter" class="select2" multiple="multiple"
                data-placeholder="{{ __('order.filter_status_placeholder') }}"
                style="width: 100%">
                @foreach ($orderStatus as $key => $value)
                <option value="{{ $value }}">{{ __('order.status_list')[$key] }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="order-table">
            <thead>
              <tr>
                <th>{{ __('order.code') }}</th>
                <th>{{ __('order.order_customer') }}</th>
                <th>{{ __('order.payment_method') }}</th>
                <th>{{ __('order.payment_time') }}</th>
                <th>{{ __('order.amount') }}</th>
                <th class="status">{{ __('order.status') }}</th>
                <th>{{ __('order.created_at') }}</th>
                <th>{{ __('order.action') }}</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@include('common.loadingSpinner')
@stop

@section('js')
<script>
  $(function() {
    $('.select2').select2()
    const columnDefs = [{
        data: 'code',
        name: 'code',
        "searchable": true
      },
      {
        data: 'orderCustomer',
        name: 'order_customer',
        'sortable': false,
        "searchable": false
      },
      {
        data: 'payment_method',
        name: 'payment_method',
        'sortable': false,
        "searchable": false
      },
      {
        data: 'payment_time',
        name: 'payment_time'
      },
      {
        data: 'amount',
        name: 'amount',
        "searchable": false
      },
      {
        data: 'status',
        name: 'status',
        'sortable': true
      },
      {
        data: 'created_at',
        name: 'created_at'
      },
      {
        data: 'action',
        name: 'action',
        'sortable': false,
        "searchable": false
      }
    ]
    const table = $('#order-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/order/anyData',
      pageLength: 50,
      columns: columnDefs
    })

    $('#order_code').on('blur', function(e) {
      handleChangeFilter()
    })

    $('#status-filter').on('change', function(e) {
      handleChangeFilter()
    })

    function handleChangeFilter() {
      $("#order-table").dataTable().fnDestroy()
      const code = $('#order_code').val()
      const statusList = $('#status-filter').select2("val")
      const dataRequest = {
        code,
        statusList
      }
      $('#order-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/order/anyData',
          "type": 'GET',
          "data": dataRequest
        },
        pageLength: 50,
        columns: columnDefs
      })
    }
  })
</script>
@stop