@extends('adminlte::page')

@section('title', __('course.course_management'))

@section('content_header')
<h1>{{__('course.course_management')}}</h1>
@stop

<!-- @vite(['resources/css/app.css', 'resources/js/app.js']) -->

@section('content')
@include('course.form', ['course' => null, 'categoryList' => $categoryList, 'tagList' => $tagList, 'courseStatus' => $courseStatus])
@include('common.loadingSpinner')
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- FindPond -->
<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
<!-- FileInput -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.min.css" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
@stop

@section('js')
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.js"></script>
<!-- FileInput -->
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/buffer.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/filetype.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/piexif.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/sortable.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/fileinput.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/locales/LANG.js"></script>
<script src="{{ asset('/js/util.js') }}"></script>
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
  const editor = CKEDITOR.replace('content', {
    fileTools_requestHeaders: {
      'X-CSRFToken': '{{ csrf_token() }}',
    },
    filebrowserBrowseUrl: '/browser/browse.php',
    filebrowserUploadUrl: '/courses/upload-img'
  });

  editor.on('fileUploadRequest', function(evt) {
    const token = '{{ csrf_token() }}'
    var fileLoader = evt.data.fileLoader,
      formData = new FormData(),
      xhr = fileLoader.xhr;
    xhr.setRequestHeader('x-csrf-token', '{{ csrf_token() }}');
    xhr.open('POST', fileLoader.uploadUrl, true);
    formData.append('upload', fileLoader.file, fileLoader.fileName);
    formData.append('_token', token);
    fileLoader.xhr.send(formData);
    evt.stop();
  });

  const authorEditor = CKEDITOR.replace('authorDescription', {
    fileTools_requestHeaders: {
      'X-CSRFToken': '{{ csrf_token() }}',
    },
    filebrowserUploadUrl: '/courses/upload-img',
  });

  authorEditor.on('fileUploadRequest', function(evt) {
    const token = '{{ csrf_token() }}'
    var fileLoader = evt.data.fileLoader,
      formData = new FormData(),
      xhr = fileLoader.xhr;
    xhr.setRequestHeader('x-csrf-token', '{{ csrf_token() }}');
    xhr.open('POST', fileLoader.uploadUrl, true);
    formData.append('upload', fileLoader.file, fileLoader.fileName);
    formData.append('_token', token);
    fileLoader.xhr.send(formData);
    evt.stop();
  });

  $('.select2').select2()
  const form = $('#form-post')
  const original = form.serialize()
  let isClickedSubmit = false

  //   handle upload room image
  const maxCapacity = {{ \Config::get('constants.max_capacity_image_upload') }}

  var meta_token = $("meta[name=csrf-token]");
  $("#input-pd").fileinput({
    maxFileSize: maxCapacity,
    allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],
    uploadAsync: true,
    showUpload: false,
    showRemove: false,
    minFileCount: 0,
    maxFileCount: 1,
    overwriteInitial: false,
    uploadExtraData: function() {
      return {
        '_token': $('input[name="_token"]').val(),
      }
    },
    initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup,
    initialPreviewFileType: 'image', // image is the default and can be overridden in config below
  }).on('fileuploaded', function(e, params) {
    console.log('File uploaded params', params);
  });

  $("#input-banner-pd").fileinput({
    maxFileSize: maxCapacity,
    allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],
    uploadAsync: true,
    showUpload: false,
    showRemove: false,
    minFileCount: 0,
    maxFileCount: 1,
    overwriteInitial: false,
    uploadExtraData: function() {
      return {
        '_token': $('input[name="_token"]').val(),
      }
    },
    initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup,
    initialPreviewFileType: 'image', // image is the default and can be overridden in config below
  }).on('fileuploaded', function(e, params) {
    console.log('File uploaded params', params);
  });

  $('#saleOffPrice').on("change", function() {
    $('#originalPrice').valid();
  });

  $('#video-body-list').sortable()

  $('#form-course').validate({
    rules: {
      title: {
        required: true,
        maxlength: 255,
      },
      description: {
        maxlength: 1000,
        required: true,
      },
      content: {
        required: true,
      },
      originalPrice: {
        greaterThan: "#saleOffPrice",
      },
      status: {
        required: true,
      },
      author: {
        maxlength: 255,
      },
    },
    messages: {
      originalPrice: {
        greaterThan: "Giá gốc phải cao hơn hoặc bằng giá sale."
      },
    },
    submitHandler: function(form) {
      // Form is valid, so you can submit it
      let videoList = []
      $('.row-episode').each(function(i, obj) {
        const epTitle = $(obj).find('.ep-title').val();
        const epDescription = $(obj).find('.ep-description').val();
        const epThumbnail = $(obj).find('.ep-thumbnail').attr('src');
        const vimeoId = $(obj).data('vimeo-id')
        videoList.push({
          epTitle,
          epDescription,
          vimeoId,
          epThumbnail
        })
      })
      videoList = JSON.stringify(videoList)
      $('.input-video-list').val(videoList)
      $("#input-pd").fileinput('upload');
      $("#input-banner-pd").fileinput('upload');
      form.submit();
    },
  });

  window.onbeforeunload = function() {
    if (form.serialize() != original && !isClickedSubmit)
      return 'Are you sure you want to leave?'
  }

  function showVideoDetail(vimeoId) {
    $.ajax({
      url: '/video/vimeo/detail/' + vimeoId,
      type: 'get',
      success: function(response) {
        if (response.status) {
          $('.custom-body-content').append(response.data);
          $('#exampleModal').modal('show');
        } else {
          Swal.fire('fail!', response.message, '')
        }
      }
    });
  }

  let table;
  $('#modal-select-video').on('show.bs.modal', function(e) {
    setTimeout(() => {
      if ($.fn.dataTable.isDataTable('#video-table')) {
        $('#video-table').DataTable().clear().destroy();
      }
      const vimeoIdSelectedList = checkedData.map(item => item.vimeo_id);
      table = $('#video-table').DataTable({
        serverSide: true,
        fixedHeader: true,
        paging: true,
        scrollY: '500px',
        scrollCollapse: true,
        ajax: '/video/anyDataForCreate',
        pageLength: 50,
        order: [],
        columns: [{
            data: 'check',
            name: 'check',
            target: "no-sort",
            orderable: false,
            className: 'checkbox-wrap'
          },
          {
            data: 'id',
            name: 'id',
            "searchable": true,
            width: '8%',
          },
          {
            data: 'title',
            name: 'title',
            "searchable": true,
            'sortable': true
          },
          {
            data: 'videoThumbnail',
            name: 'videoThumbnail',
            'sortable': false
          },
          {
            data: 'created_at',
            name: 'created_at'
          },
        ],
        createdRow: function(row, data, dataIndex) {
          $(row).data('vimeo-id', data['vimeo_id'])
          if (vimeoIdSelectedList.includes(data['vimeo_id'])) {
            $(row).addClass('has-selected');
            $(row).find('.form-checkbox-input').prop('checked', true);
            $(row).find('.form-checkbox-input').attr('disabled', true)
          }
        }
      });

    }, 200)
  })

  $('#modal-select-video').on('hidden.bs.modal', function(e) {
    // Clear the DataTable
    if (table) {
      table.clear().draw();
    }
  });

  $('#video-table').on('click', '.btn-info-video', function(e) {
    e.preventDefault();
    $('.custom-body-content').html('');
    const id = $(this).attr('video-id');
    showVideoDetail(id);
  });

  let checkedData = [];
  $('.table-result').addClass('hidden')
  $('.btn-select-course-video').on('click', function() {
    $('#video-table .form-checkbox-input:checked').each(function() {
      var row = $(this).closest('tr');
      var rowData = table.row(row).data();
      const vimeoIdSelectedList = checkedData.map(item => item.vimeo_id);
      if (!vimeoIdSelectedList.includes(rowData['vimeo_id'])) {
        checkedData.push(rowData);
      }
    });
    updateVideoBodyList()
    $('#modal-select-video').modal('hide')
  })

  function updateVideoBodyList() {
    let rowHtml = ''
    console.log(444, checkedData)
    for (let i = 0; i < checkedData.length; i++) {
      const {
        title,
        thumbnail_id,
        vimeo_id
      } = checkedData[i]
      rowHtml += '<tr class="row-episode" data-vimeo-id="' + vimeo_id + '">' +
        '<td>' +
        '<input type="text" class="ep-title form-control" value="' + title + '" />' +
        '</td>' +
        '<td>' +
        '<img src="' + thumbnail_id + '" width="100" height="100" class="ep-thumbnail" />' +
        '</td>' +
        '<td>' +
        '<textarea class="ep-description form-control" rows="4" cols="50"></textarea>' +
        '</td>' +
        '<td>' +
        '<button type="button" class="btn btn-block btn-info btn-info-video" style=" width: 130px; " video-id="' + vimeo_id + '">Xem video</button>' +
        '</td>' +
        '<td>' +
        '<i class="fas fa-trash red-icon btn-delete-video"></i>' +
        '</td>' +
        '</tr>'
    }
    $('#video-body-list').empty().html(rowHtml)
    if (checkedData.length === 0) {
      $('.table-result').addClass('hidden')
    } else {
      $('.table-result').removeClass('hidden')
    }
  }

  $('#video-body-list').on('click', '.btn-delete-video', function(e) {
    const $this = $(this)
    Swal.fire({
      title: 'Xác nhận xóa tập',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Xác nhận',
      cancelButtonText: "Đóng",
    }).then((result) => {
      if (result['isConfirmed']) {
        const row = $(this).closest('tr');
        const vimeoId = row.data('vimeo-id')
        const currentIdx = checkedData.findIndex((el) => {
          return el.vimeo_id === vimeoId;
        })
        checkedData.splice(currentIdx, 1)
        updateVideoBodyList()
      }
    })
  })

  $('#video-body-list').on('click', '.btn-info-video', function(e) {
    e.preventDefault();
    $('.custom-body-content').html('');
    const id = $(this).attr('video-id');
    showVideoDetail(id);
  });

  $('#exampleModal').on('hidden.bs.modal', function(e) {
    $('.custom-body-content').html('');
  });

  function showVideoDetail(vimeoId) {
    $.ajax({
      url: '/video/vimeo/detail/' + vimeoId,
      type: 'get',
      success: function(response) {
        if (response.status) {
          $('.custom-body-content').append(response.data);
          $('#exampleModal').modal('show');
        } else {
          Swal.fire('fail!', response.message, '')
        }
      }
    });
  }
</script>

@stop

@section('css')
<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> -->
<!-- <link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}"> -->
<style lang="css">
  .hidden {
    display: none !important;
  }
</style>
@stop