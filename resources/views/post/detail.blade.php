@extends('adminlte::page')

@section('title', __('post.post_management'))

@section('content_header')
<h1>{{__('post.post_management')}}</h1>
@stop

@section('content')
@include('post.form', ['post' => $post, 'categoryList' => $categoryList,
'postStatus' => $postStatus,
'tagList' => $tagList])

@include('common.loadingSpinner')
@stop
@section('css')
<link rel="stylesheet" href="{{ asset('css/receipt.add.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css" rel="stylesheet">
<!-- FindPond -->
<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" />
<!-- FileInput -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.min.css" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <!-- FileInput -->
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/buffer.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/filetype.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/piexif.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/plugins/sortable.min.js" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/fileinput.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/locales/LANG.js"></script>
<script>
    const editor = CKEDITOR.replace('content', {
    fileTools_requestHeaders: {
      'X-CSRFToken': '{{ csrf_token() }}',
    },
    filebrowserBrowseUrl: '/browser/browse.php',
    filebrowserUploadUrl: '/posts/upload-img',
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

  $('.select2').select2()
  const form = $('#form-post')
  const original = form.serialize()
  let isClickedSubmit = false

  //   handle upload room image
  const maxCapacity = {{ \Config::get('constants.max_capacity_image_upload') }}
  const post = @json($post);
  console.log(post);

  if (post) {
    const initialPreview = [post.thumbnail]
    const initialPreviewConfig = [{
      caption: post.thumbnail,
      width: "120px",
      url: "/posts/delete-img/" + post.id,
      key: post.id,
      extra: {
        '_token': $('input[name="_token"]').val()
      }
    }]
    var meta_token = $("meta[name=csrf-token]");
    $("#input-pd").fileinput({
      browseClass: "btn btn-primary", // Màu cho nút chọn ảnh
      removeClass: "btn btn-danger",
      maxFileSize: maxCapacity,
      allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],
      uploadAsync: true,
      showUpload: false,
      showRemove: true,
      minFileCount: 0,
      maxFileCount: 1,
      overwriteInitial: false,
      uploadExtraData: function() {
        return {
          '_token': $('input[name="_token"]').val(),
          'post_id': post.id
        }
      },
      initialPreview: initialPreview,
      initialPreviewAsData: true,
      initialPreviewConfig: initialPreviewConfig,
      initialPreviewFileType: 'image',
    }).on('fileuploaded', function(e, params) {
      console.log('File uploaded params', params);
    }).on('click', '.fileinput-remove', function(event, key, jqXHR, data) {
      console.log("File đã bị xóa:", key);
      var postId = $('#id').val();
      handleDelThumbnail(postId)
    });
  }

  function handleDelThumbnail(postId) {
      $.ajax({
        url: '/posts/delete-img/' + postId,
        type: 'post',
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "id": postId
        },
        success: function(response) {
          if (response.status) {
            console.log(response)
            handlerFilter()
            const msgDeleteSuccess = "<?php echo __('post.message.delete_post_success') ?>"
            Swal.fire(msgDeleteSuccess, '', 'success')
          } else {
            Swal.fire('fail!', response.message, '')
          }
        }
      });
    }

  $('#form-post').validate({
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
      status: {
        required: true,
      }
    },
  });


  window.onbeforeunload = function() {
    if (form.serialize() != original && !isClickedSubmit)
      return 'Are you sure you want to leave?'
  }
  
</script>
@stop