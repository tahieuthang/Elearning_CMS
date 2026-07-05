<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-primary">
        <div class="card-header">
          @if($course)
          <h3 class="card-title">{{ __('course.detail_course') }} <small>{{ $course->title }}</small></h3>
          @else
          <h3 class="card-title">{{ __('course.create_course') }}</h3>
          @endif
        </div>
        <!-- /.card-header -->

        @if(count($errors) > 0 )
        <div class="card-body">
          <div class="form-group">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <ul class="m-0">
                @foreach($errors->all() as $error)
                <li>{{$error}}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
        @endif
        @if(session('success'))
        <div class="card-body">
          <div class="form-group">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <ul class="m-0">
                <li>{{session('success')}}</li>
              </ul>
            </div>
          </div>
        </div>
        @endif

        <form id="form-course" method="POST" action="{{ $course ? route('courses.update', ['id' => $course->id]) : route('courses.createCourse') }}" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="card-body">
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" id="courseFormTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info-pane" role="tab" aria-controls="info-pane" aria-selected="true">
                  <i class="fas fa-info-circle mr-1"></i> Thông tin chung
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="quiz-tab" data-toggle="tab" href="#quiz-pane" role="tab" aria-controls="quiz-pane" aria-selected="false">
                  <i class="fas fa-question-circle mr-1"></i> Quản lý Quiz
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="curriculum-tab" data-toggle="tab" href="#curriculum-pane" role="tab" aria-controls="curriculum-pane" aria-selected="false">
                  <i class="fas fa-graduation-cap mr-1"></i> Chương trình học
                </a>
              </li>
              @if($course)
              <li class="nav-item">
                <a class="nav-link" id="coupon-tab" data-toggle="tab" href="#coupon-pane" role="tab" aria-controls="coupon-pane" aria-selected="false">
                  <i class="fas fa-ticket-alt mr-1"></i> Quản lý Coupon
                </a>
              </li>
              @endif
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="courseFormTabsContent">
              <!-- Tab 1: Info Pane -->
              <div class="tab-pane fade show active" id="info-pane" role="tabpanel" aria-labelledby="info-tab">
                @if($course)
                <div class="form-group">
                  <label for="id">{{ __('course.id') }}</label>
                  <input type="text" value="{{ $course->id }}" disabled class="form-control" id="id">
                </div>
                @endif
                <div class="form-group">
                  <label for="course-name">{{ __('course.name') }}</label>
                  <input type="text" value="{{old('title', $course ? $course->title : '')}}" name="title" class="form-control" id="course-name" placeholder="{{ __('course.form_placeholder.name_placeholder') }}">
                </div>

                <div class="form-group">
                  <label for="description">{{ __('course.description') }}</label>
                  <textarea name="description" id="description" cols="20" rows="5" class="form-control" placeholder="{{ __('course.form_placeholder.description_placeholder') }}">{{old('description', $course ? $course->description : '')}}</textarea>
                </div>

                <div class="col-6 form-group px-0">
                  <label for="category-filter">{{ __('course.course_category') }}</label>
                  <select id="category-filter" name="courseCategories[]" class="select2 form-control" multiple="multiple" data-placeholder="{{ __('course.filter_category_placeholder') }}" style="width: 100%;">
                    <?php $arrChecked = old('courseCategories', $course ? $course->courseCategories->pluck('id')->all() : []) ?>
                    @foreach($categoryList as $category)
                    <option <?php if (in_array($category->id, $arrChecked)) { echo 'selected'; } ?> value="{{ $category->id }}">{{ $category->category_name }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group">
                  <label for="originalPrice">{{ __('course.original_price') }}</label>
                  <input value="{{ old('originalPrice', $course ? \App\Helpers\Helper::convertMoney($course->original_price, '') : '') }}" id="originalPrice" name="originalPrice" type="text" class="form-control js-money-input" inputmode="numeric" autocomplete="off" placeholder="0">
                </div>

                <div class="form-group">
                  <label for="saleOffPrice">{{ __('course.sale_off_price') }}</label>
                  <input value="{{ old('saleOffPrice', $course ? \App\Helpers\Helper::convertMoney($course->sale_off_price, '') : '') }}" id="saleOffPrice" name="saleOffPrice" type="text" class="form-control js-money-input" inputmode="numeric" autocomplete="off" placeholder="0">
                </div>

                <div class="form-group">
                  <label for="courseDuration">{{ __('course.course_duration') }}</label>
                  <input id="courseDuration" value="{{old('courseDuration', $course ? $course->course_duration : '')}}" name="courseDuration" type="text" class="form-control">
                </div>

                <div class="form-group">
                  <label for="tag-filter">{{ __('course.course_tag') }}</label>
                  <select id="tag-filter" name="courseTags[]" class="select2 form-control" multiple="multiple" data-placeholder="{{ __('course.form_placeholder.tag_placeholder') }}" style="width: 100%;">
                    <?php $arrChecked = old('courseTags', $course ? $course->courseTags->pluck('id')->all() : []) ?>
                    @foreach($tagList as $tag)
                    <option <?php if (in_array($tag->id, $arrChecked)) { echo 'selected'; } ?> value="{{ $tag->id }}">{{ $tag->tag_name }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group">
                  <label for="status-filter">{{ __('course.status') }}</label>
                  <select id="status-filter" name="status" class="select2 form-control" data-placeholder="{{ __('post.status_placeholder') }}" style="width: 100%;">
                    @foreach($courseStatus as $id => $status)
                    <option <?php if ($course && $course->status == $id) { echo 'selected'; } ?> value="{{ $id }}">{{ __('course.status_list')[$id] }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group">
                  <label for="input-pd">{{ __('course.thumbnail') }}</label>
                  <div class="file-loading">
                    <input id="input-pd" name="input-pd[]" type="file">
                  </div>
                </div>

                <div class="form-group">
                  <label for="input-banner-pd">{{ __('course.banner') }}</label>
                  <div class="file-loading">
                    <input id="input-banner-pd" name="input-banner-pd[]" type="file">
                  </div>
                </div>

                <div class="form-group">
                  <label for="content">{{ __('course.content') }}</label>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="card card-outline card-info">
                        <div class="card-header">
                          <h3 class="card-title">
                            {{ __('course.ck_editor_header') }}
                          </h3>
                        </div>
                        <div class="card-body">
                          <textarea id="content" name="content">{{old('content', $course ? $course->content : '')}}</textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="author">Tên tác giả</label>
                  <input type="text" value="{{old('author', $course ? $course->author : '')}}" name="author" class="form-control" id="author" placeholder="Nhập tên tác giả">
                </div>

                <div class="form-group">
                  <label for="authorDescription">Giới thiệu về tác giả</label>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="card card-outline card-info">
                        <div class="card-header">
                          <h3 class="card-title">
                            {{ __('course.ck_editor_header') }}
                          </h3>
                        </div>
                        <div class="card-body">
                          <textarea id="authorDescription" name="authorDescription">{{old('authorDescription', $course ? $course->authorDescription : '')}}</textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tab 2: Quiz Pane -->
              <div class="tab-pane fade" id="quiz-pane" role="tabpanel" aria-labelledby="quiz-tab">
                <div class="card card-secondary">
                  <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">Danh sách bài Quiz</h3>
                    <button type="button" class="btn btn-success btn-sm ml-auto" id="btn-add-quiz">
                      <i class="fas fa-plus mr-1"></i> Thêm Quiz mới
                    </button>
                  </div>
                  <div class="card-body bg-light" id="quiz-list-container" style="min-height: 200px;">
                    <!-- Quiz Cards will be dynamically rendered here -->
                  </div>
                </div>
              </div>

              <!-- Tab 3: Curriculum Pane -->
              <div class="tab-pane fade" id="curriculum-pane" role="tabpanel" aria-labelledby="curriculum-tab">
                <input type="hidden" name="curriculum-list" class="input-curriculum-list" />
                <input type="hidden" name="video-list" class="input-video-list" />

                <div class="card card-secondary">
                  <div class="card-header d-flex align-items-center">
                    <h3 class="card-title">Sắp xếp Curriculum (Kéo thả để sắp xếp)</h3>
                    <button type="button" class="btn btn-info btn-sm ml-auto" data-toggle="modal" data-target="#modal-select-video">
                      <i class="fas fa-video mr-1"></i> Chọn Video
                    </button>
                  </div>
                  <div class="card-body">
                    <div class="alert alert-info py-2">
                      <i class="fas fa-info-circle mr-1"></i> Bạn có thể thêm video bằng nút <strong>Chọn Video</strong>, tạo quiz ở tab <strong>Quản lý Quiz</strong>, sau đó kéo thả các mục bên dưới để sắp xếp thứ tự hiển thị.
                    </div>
                    <ul id="curriculum-list-ui" class="list-group">
                      <!-- Unified sortable list will be rendered here by JS -->
                    </ul>
                    <div id="empty-curriculum-msg" class="text-center py-5 text-muted">
                      <i class="fas fa-folder-open fa-3x mb-3"></i>
                      <p>Chưa có nội dung nào trong khóa học này. Hãy thêm video hoặc quiz!</p>
                    </div>
                  </div>
                </div>

                <!-- Legacy video-list elements keeping them just in case -->
                <div class="table table-wrapper d-none">
                  <table class="table table-bordered w-full">
                    <tbody id="video-body-list"></tbody>
                  </table>
                </div>
              </div>

              @if($course)
              <!-- Tab 4: Coupon Pane -->
              <div class="tab-pane fade" id="coupon-pane" role="tabpanel" aria-labelledby="coupon-tab">
                <div class="row">
                  <!-- Column 1: Create Coupon Form -->
                  <div class="col-md-4">
                    <div class="card card-secondary card-outline">
                      <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-plus-circle mr-1"></i> Tạo Coupon Mới</h3>
                      </div>
                      <div class="card-body">
                        <div id="form-create-coupon">
                          <div class="form-group mb-3">
                            <label for="coupon-code-input" class="form-label font-weight-bold">Mã Coupon <span class="text-danger">*</span></label>
                            <div class="input-group">
                              <input type="text" id="coupon-code-input" class="form-control text-uppercase" placeholder="VD: NODEJS50">
                              <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="btn-generate-coupon-code">
                                  <i class="fas fa-magic"></i> Ngẫu nhiên
                                </button>
                              </div>
                            </div>
                          </div>

                          <div class="form-group mb-3">
                            <label for="coupon-discount-type" class="form-label font-weight-bold">Loại giảm giá <span class="text-danger">*</span></label>
                            <select id="coupon-discount-type" class="form-control">
                              <option value="percent">Phần trăm (%)</option>
                              <option value="fixed">Số tiền cố định (đ)</option>
                            </select>
                          </div>

                          <div class="form-group mb-3">
                            <label for="coupon-discount-value" class="form-label font-weight-bold">Giá trị giảm <span class="text-danger">*</span></label>
                            <input type="number" id="coupon-discount-value" class="form-control" placeholder="VD: 50 hoặc 100000" min="0">
                          </div>

                          <div class="form-group mb-3">
                            <label for="coupon-max-uses" class="form-label font-weight-bold">Lượt dùng tối đa <span class="text-danger">*</span></label>
                            <input type="number" id="coupon-max-uses" class="form-control" placeholder="VD: 10 (0 = Không giới hạn)" min="0" value="0">
                          </div>

                          <div class="form-group mb-3">
                            <label for="coupon-expires-at" class="form-label font-weight-bold">Ngày hết hạn</label>
                            <input type="datetime-local" id="coupon-expires-at" class="form-control">
                          </div>

                          <button type="button" class="btn btn-success btn-block mt-4" id="btn-submit-coupon">
                            <i class="fas fa-save mr-1"></i> Tạo Mã Coupon
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Column 2: Coupon List -->
                  <div class="col-md-8">
                    <div class="card card-secondary card-outline">
                      <div class="card-header d-flex align-items-center">
                        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Danh sách Coupon của Khóa học</h3>
                        <button type="button" class="btn btn-tool ml-auto" id="btn-refresh-coupons">
                          <i class="fas fa-sync-alt"></i> Làm mới
                        </button>
                      </div>
                      <div class="card-body p-0">
                        <div class="table-responsive">
                          <table class="table table-hover table-striped mb-0" id="coupon-list-table">
                            <thead class="thead-light">
                              <tr>
                                <th>Mã</th>
                                <th>Loại giảm giá</th>
                                <th>Giá trị giảm</th>
                                <th>Đã dùng / Tổng</th>
                                <th>Hết hạn</th>
                                <th>Trạng thái</th>
                                <th class="text-right">Thao tác</th>
                              </tr>
                            </thead>
                            <tbody id="coupon-table-body">
                              <!-- Will be populated by JS -->
                            </tbody>
                          </table>
                          <div id="empty-coupons-msg" class="text-center py-5 text-muted d-none">
                            <i class="fas fa-ticket-alt fa-3x mb-3 text-secondary"></i>
                            <p class="mb-0">Chưa có mã giảm giá nào cho khóa học này.</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endif
            </div>

            <!-- Video Selection Modal -->
            <div id="modal-select-video" class="modal fade" role="dialog">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Danh sách video</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                  </div>
                  <div class="modal-body modal-body-select-video">
                    <div class="card-body">
                      <div class="dataTables_wrapper dt-bootstrap4">
                        <table class="table table-bordered w-100" id="video-table">
                          <thead>
                            <tr>
                              <th>Chọn</th>
                              <th>{{__('video.id')}}</th>
                              <th>{{__('video.title')}}</th>
                              <th>{{__('video.videoThumbnail')}}</th>
                              <th>{{__('video.created_at')}}</th>
                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>
                    <div class="modal-footer justify-content-end">
                      <button type="button" class="btn btn-primary btn-select-course-video">Xác nhận</button>
                      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Video Detail Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Chi tiết video</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div class="custom-body-content"></div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Edit Video Meta Modal -->
            <div id="modal-edit-video-meta" class="modal fade" role="dialog" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Chỉnh sửa thông tin Video</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" id="edit-video-item-idx" />
                    <div class="form-group">
                      <label for="edit-video-title">Tiêu đề Video <span class="text-danger">*</span></label>
                      <input type="text" id="edit-video-title" class="form-control" placeholder="Nhập tiêu đề video..." />
                    </div>
                    <div class="form-group">
                      <label for="edit-video-desc">Mô tả Video</label>
                      <textarea id="edit-video-desc" class="form-control" rows="3" placeholder="Mô tả ngắn về video..."></textarea>
                    </div>
                    <div class="form-group">
                      <label for="edit-video-thumb-file">Ảnh đại diện (Thumbnail) cho Video</label>
                      <div class="d-flex align-items-center mb-2">
                        <input type="file" id="edit-video-thumb-file" class="form-control-file" accept="image/*" style="width: auto;" />
                        <button type="button" class="btn btn-outline-primary btn-sm ml-2" id="btn-upload-video-thumb">
                          <i class="fas fa-upload mr-1"></i> Tải ảnh lên
                        </button>
                      </div>
                      <input type="text" id="edit-video-thumb-url" class="form-control" placeholder="Đường dẫn ảnh thumbnail (hoặc tải ảnh từ file trên)..." />
                      <div class="mt-2 text-center">
                        <img id="edit-video-thumb-preview" src="" style="max-width: 240px; max-height: 135px; display: none; border: 1px solid #ddd; border-radius: 4px;" />
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btn-save-video-meta">Lưu thay đổi</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- /.card-body -->
            <div class="card-footer mt-4">
              <button class="btn btn-primary btn-submit-course" type="submit">{{ $course ? __('course.update_course') : __('course.create_course') }}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  .file-container {
    width: 100%;
  }

  #curriculum-list-ui {
    min-height: 100px;
  }

  .curriculum-item {
    cursor: move;
    border-left: 4px solid #007bff;
    transition: background-color 0.2s;
  }
  .curriculum-item.quiz-type {
    border-left-color: #ffc107;
  }
  .curriculum-item:hover {
    background-color: #f1f3f5;
  }
  .curriculum-item .handle {
    color: #6c757d;
  }

  .red-icon {
    color: red;
    cursor: pointer;
  }

  .has-selected {
    opacity: 0.5;
  }

  .table-wrapper {
    max-height: 600px;
    overflow-y: auto;
  }

  .quiz-card {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    background: #fff;
    margin-bottom: 1.5rem;
    transition: all 0.2s;
  }
  .quiz-card:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
  }

  .question-card {
    border: 1px dashed #ced4da;
    background: #f8f9fa;
    border-radius: 0.25rem;
    padding: 1rem;
    margin-bottom: 1rem;
  }

  .option-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0.5rem;
  }
</style>