@extends('adminlte::page')

@section('title', __('video.video_process_management'))

@section('content_header')
<h1>{{__('video.video_process_management')}}</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="process-table">
            <thead>
              <tr>
                <th>{{__('video.id')}}</th>
                <th>{{__('video.title')}}</th>
                <th>{{__('video.created_at')}}</th>
                <th>{{__('video.error_log')}}</th>
                <th>{{__('video.status')}}</th>
                <th>{{__('video.action')}}</th>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
  $(function() {
    const table = $('#process-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/video/process/data',
      pageLength: 50,
      order: [
        [2, 'desc']
      ],
      columns: [{
          data: 'id',
          name: 'id',
        },
        {
          data: 'video_id',
          name: 'video_id',
        },
        {
          data: 'created_at',
          name: 'created_at',
        },
        {
          data: 'error_log',
          name: 'error_log',
        },
        {
          data: 'job_status',
          name: 'job_status',
          'className': 'text-center',
          'sortable': false
        },
        {
          data: 'action',
          name: 'action',
          width: '100',
          'sortable': false
        }
      ]
    });
  });
</script>
@stop