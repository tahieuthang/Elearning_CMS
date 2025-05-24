@extends('adminlte::page')

@section('title', __('payment_transaction.payment_transaction_management'))

@section('content_header')
<h1>{{ __('payment_transaction.payment_transaction_management') }}</h1>
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
            <label>{{ __('payment_transaction.filter_order_code') }}</label>
            <div class="select2-purple">
              <input class="form-control" type="text" name="order_code" id="order_code"
                placeholder="{{ __('payment_transaction.filter_order_code_placeholder') }}" />
            </div>
          </div>
          <div class="col-6 form-group">
            <label>{{ __('payment_transaction.filter_status') }}</label>
            <div class="select2-purple">
              <select id="status-filter" class="select2" multiple="multiple"
                data-placeholder="{{ __('payment_transaction.filter_status_placeholder') }}"
                data-dropdown-css-class="select2-purple" style="width: 100%">
                @foreach ($paymentTransactionStatus as $key => $value)
                <option value="{{ $value }}">{{ __('payment_transaction.status_list')[$key] }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="payment-transaction-table">
            <thead>
              <tr>
                <th>{{ __('payment_transaction.code') }}</th>
                <th>{{ __('payment_transaction.order_code') }}</th>
                <th>{{ __('payment_transaction.customer') }}</th>
                <th>{{ __('payment_transaction.payment_method') }}</th>
                <th>{{ __('payment_transaction.amount') }}</th>
                <th class="status">{{ __('payment_transaction.status') }}</th>
                <th>{{ __('payment_transaction.updated_at') }}</th>
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
        data: 'orderCode',
        name: 'order_code',
        'sortable': false,
        "searchable": false
      },
      {
        data: 'customer',
        name: 'customer',
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
        data: 'updated_at',
        name: 'updated_at'
      },
    ]
    const table = $('#payment-transaction-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/payment-transaction/anyData',
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
      $("#payment-transaction-table").dataTable().fnDestroy()
      const order_code = $('#order_code').val()
      const statusList = $('#status-filter').select2("val")
      const dataRequest = {
        order_code,
        statusList
      }
      $('#payment-transaction-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/payment-transaction/anyData',
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