@extends('adminlte::page')

@section('title', __('role.role_management'))

@section('content_header')
<h1>{{__('role.role_management')}}</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@stop

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row">
          @if(\App\Helpers\Helper::checkPermission('role.create'))
          <a class="ml-auto" href="{{ route('role.create') }}">
            <button class="btn btn-success">{{ __('role.create_role') }}</button>
          </a>
          @endif
        </div>
      </div>
      <div class="card-body">
        <div class="dataTables_wrapper dt-bootstrap4">
          <table class="table table-bordered" id="role-table">
            <thead>
              <tr>
                <th>{{__('role.id')}}</th>
                <th>{{__('role.role_name')}}</th>
                <th>{{__('role.description')}}</th>
                <th>{{__('role.created_at')}}</th>
                <th>{{__('role.action')}}</th>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
  $(function() {
    $('.select2').select2()
    const columnDefs = [{
        data: 'id',
        name: 'id',
        "searchable": true,
        'sortable': false,
      },
      {
        data: 'role_name',
        name: 'role_name',
        "searchable": true,
        'sortable': true,
      },
      {
        data: 'description',
        name: 'description',
        "searchable": false,
        'sortable': false,
      },
      {
        data: 'created_at',
        name: 'created_at',
        "searchable": true,
        'sortable': false,
      },
      {
        data: 'action',
        name: 'action',
        'sortable': false,
        "searchable": false
      }
    ]
    const table = $('#role-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      searchDelay: 800,
      ajax: '/role/anyData',
      pageLength: 50,
      columns: columnDefs
    });

    function handleChangeFilter() {
      $("#role-table").dataTable().fnDestroy();
      $('#role-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        searchDelay: 800,
        ajax: {
          "url": '/role/anyData',
          "type": 'GET',
        },
        pageLength: 50,
        columns: columnDefs
      });
    }

    function deleteRole(roleId) {
      $.ajax({
        url: '/role/delete/' + roleId,
        type: 'delete',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "id": roleId
        },
        success: function(response) {
          if (response.status) {
            handleChangeFilter()
            const msgDeleteSuccess = "<?php echo __('role.message.delete_role_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

    $('#role-table').on('click', '.btn-delete-role', function(e) {
      e.preventDefault();
      const id = $(this).data('id')
      const name = $(this).data('name')
      const msgConfirmDelete = "<?php echo __('role.message.delete_role_confirm_js'); ?>" + ' ' + name + ' ?'
      Swal.fire({
        title: msgConfirmDelete,
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "<?php echo __('role.btn_confirm'); ?>",
        cancelButtonText: "<?php echo __('role.btn_cancel'); ?>",
      }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
          deleteRole(id)
        } else if (result.isDenied) {
          Swal.fire('Changes are not saved', '', 'info')
        }
      })
    });
  });
</script>
@stop