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
@stop
@section('js')
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