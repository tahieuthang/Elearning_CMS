@extends('adminlte::page')

@section('title', __('video.video_management'))

@section('content_header')
<h1>{{ __('video.video_management') }}</h1>
@stop

@section('content')
<div class="timeline">
    <!-- Timeline time label -->
    <div class="time-label">
        <span class="bg-green">{{ \Carbon\Carbon::now()->format('d M. Y') }}</span>
    </div>
    <div>
        <!-- Before each timeline item corresponds to one icon on the left scale -->
        <i class="fa fa-camera bg-purple"></i>
        <!-- Timeline item -->
        <div class="timeline-item">
            <!-- Time -->
            <span class="time"><i class="fas fa-clock"></i> {{ \Carbon\Carbon::now()->format('h:i') }}</span>
            <!-- Header. Optional -->
            <h3 class="timeline-header"><a href="#">{{ auth()->user()->username }}</a> is uploading video</h3>
            <!-- Body -->
            <div class="timeline-body">
                <div class="form-group">
                    <label for="room-name">Chọn tối đa 5 video, mỗi video tối đa 500mb</label>
                    <div class="file-loading">
                        <input id="input-pd" name="input-pd[]" type="file" multiple>
                    </div>
                </div>
            </div>
            <!-- Placement of additional controls. Optional -->
        </div>
    </div>
    @include('common.loadingSpinner')
    {{-- <div class="modal fade show" id="modal-default" style="display: block; padding-right: 15px;" aria-modal="true"
            role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Default Modal</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>One fine body…</p>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>

            </div>
        </div> --}}
</div>
@stop

@section('js')
<script src="{{ asset('/js/util.js') }}"></script>
<script>
    $('.select2').select2()
    const form = $('#form-post')
    const original = form.serialize()
    let isClickedSubmit = false

    //   handle upload room image
    const maxCapacity = {{ \Config::get('constants.max_capacity_video_upload') }}

    var meta_token = $("meta[name=csrf-token]");
    $("#input-pd").fileinput({
            maxFileSize: maxCapacity,
            enableResumableUpload: true,
            allowedPreviewTypes: ['video'],
            allowedFileExtensions: ['MP4', 'MOV', 'AVI', 'gif', 'WebM', 'WMV', 'FLV'],
            showCancel: true,
            uploadAsync: true,
            showUpload: true,
            showRemove: false,
            maxFileCount: 5,
            overwriteInitial: false,
            initialPreview: [],
            initialPreviewConfig: [],
            uploadUrl: '/video/uploadVideo',
            uploadExtraData: function() {
                return {
                    '_token': $('input[name="_token"]').val(),
                }
            },
            initialPreviewAsData: true, // identify if you are sending preview data only and not the raw markup,
            initialPreviewFileType: 'video', // image is the default and can be overridden in config below
        }).on('fileuploaded', function(event, previewId, index, fileId, fileName) {
            let filename = fileId.split('_').slice(1).join('_');
            filename = '/uploads/' + filename;
            saveVideoIdAfterUpload(fileId, filename)
        }).on('fileuploaderror', function(event, data, msg) {
            Swal.fire('fail!', msg, '')
        })
        .on('filebatchuploadcomplete', function(event, preview, config, tags, extraData) {
            Swal.fire({
                title: "Tải video lên server đã hoàn tất, đang xử lý nội dung video trên VIMEO. Bạn có muốn đến trang xử lý tiến trình không ?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Có",
                cancelButtonText: "Không"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "/video/process";
                }
            });
        });

    function saveVideoIdAfterUpload(videoId, filePath) {
        $.ajax({
            url: '/video/saveVideoId',
            type: 'post',
            data: {
                "_token": $('meta[name="csrf-token"]').attr('content'),
                videoId,
                filePath
            },
            success: function(response) {
                if (!response.status) {
                    Swal.fire('fail!', response.message, '')
                }
            }
        });
    }
</script>
@stop