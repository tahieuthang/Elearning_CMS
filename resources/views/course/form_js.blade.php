<script>
$(document).ready(function() {
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // CKEditor Initialization
  const editor = CKEDITOR.replace('content', {
    fileTools_requestHeaders: {
      'X-CSRFToken': '{{ csrf_token() }}',
    },
    filebrowserBrowseUrl: '/browser/browse.php',
    filebrowserUploadUrl: '/courses/upload-img'
  });

  editor.on('fileUploadRequest', function(evt) {
    const token = '{{ csrf_token() }}';
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
    const token = '{{ csrf_token() }}';
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

  $('.select2').select2();

  // FileInput for Thumbnail & Banner
  const maxCapacity = {{ \Config::get('constants.max_capacity_image_upload') }};
  const course = @json($course ?? null);

  if (course) {
    const initialPreview = course.thumbnail ? [course.thumbnail] : [];
    const initialPreviewConfig = course.thumbnail ? [{
      caption: course.thumbnail.split('/').pop(),
      width: "120px",
      url: "/courses/delete-img/" + course.id,
      key: course.id,
      extra: {
        '_token': $('input[name="_token"]').val()
      }
    }] : [];

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
          'course_id': course.id
        }
      },
      initialPreview: initialPreview,
      initialPreviewAsData: true,
      initialPreviewConfig: initialPreviewConfig,
      initialPreviewFileType: 'image',
    });

    const initialPreviewBanner = course.banner ? [course.banner] : [];
    const initialPreviewConfigBanner = course.banner ? [{
      caption: course.banner.split('/').pop(),
      width: "120px",
      url: "/courses/delete-img-banner/" + course.id,
      key: course.id,
      extra: {
        '_token': $('input[name="_token"]').val()
      }
    }] : [];

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
          'course_id': course.id
        }
      },
      initialPreview: initialPreviewBanner,
      initialPreviewAsData: true,
      initialPreviewConfig: initialPreviewConfigBanner,
      initialPreviewFileType: 'image',
    });
  } else {
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
      initialPreviewAsData: true,
      initialPreviewFileType: 'image',
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
      initialPreviewAsData: true,
      initialPreviewFileType: 'image',
    });
  }

  // Money Formatting
  function sanitizeMoneyToDigits(value) {
    if (value === null || value === undefined) return '';
    const digits = String(value).replace(/[^\d]/g, '');
    return digits.replace(/^0+(?=\d)/, '');
  }

  function bindMoneyInputs() {
    const $moneyInputs = $('.js-money-input');
    if ($moneyInputs.length === 0) return;

    $moneyInputs.each(function() {
      if (typeof window.formatMoneyInputElement === 'function') {
        window.formatMoneyInputElement(this);
      } else if (typeof window.onlyNumberAmount === 'function') {
        window.onlyNumberAmount(this);
      }
    });

    $moneyInputs.on('input', function() {
      if (typeof window.formatMoneyInputElement === 'function') {
        window.formatMoneyInputElement(this);
      } else if (typeof window.onlyNumberAmount === 'function') {
        window.onlyNumberAmount(this);
      }
    });
  }
  bindMoneyInputs();

  // --- Curriculum & Quiz Unified State Management ---
  // A single list of items: could be of type 'video' or 'quiz'
  let curriculumList = [];

  // Parse existing data if editing
  if (course) {
    const initialVideos = course.videos || [];
    const initialQuizzes = course.quizzes || [];

    initialVideos.forEach(v => {
      curriculumList.push({
        type: 'video',
        order: v.order !== undefined && v.order !== null ? parseInt(v.order) : 9999,
        epTitle: v.video_title,
        epDescription: v.video_description,
        epThumbnail: v.video_thumbnail,
        durationSeconds: v.duration_seconds,
        vimeoId: v.vimeo_id,
        dbId: v.id
      });
    });

    initialQuizzes.forEach(q => {
      let questions = [];
      if (q.questions) {
        q.questions.forEach(quest => {
          let options = [];
          if (quest.options) {
            quest.options.forEach(opt => {
              options.push({
                option_text: opt.option_text,
                is_correct: opt.is_correct == 1 || opt.is_correct === true
              });
            });
          }
          questions.push({
            question_text: quest.question_text,
            type: quest.type || 'single',
            options: options
          });
        });
      }
      curriculumList.push({
        type: 'quiz',
        order: q.order !== undefined && q.order !== null ? parseInt(q.order) : 9999,
        title: q.title,
        description: q.description,
        questions: questions,
        dbId: q.id
      });
    });

    // Sort by order initially
    curriculumList.sort((a, b) => a.order - b.order);
  }

  // Functions to render the interface based on curriculumList state
  function renderAll() {
    renderQuizTab();
    renderCurriculumTab();
  }

  // Render Quiz Manager Tab
  function renderQuizTab() {
    const $container = $('#quiz-list-container');
    $container.empty();

    let quizIndex = 0;
    curriculumList.forEach((item, index) => {
      if (item.type !== 'quiz') return;

      const quizIdx = index; // Store its true index in curriculumList
      const quiz = item;

      let quizCardHtml = `
        <div class="card quiz-card" data-item-idx="${quizIdx}">
          <div class="card-header bg-secondary text-white d-flex align-items-center">
            <h5 class="card-title mb-0">
              <i class="fas fa-question-circle mr-2"></i>Quiz #${quizIndex + 1}: <span class="quiz-display-title">${quiz.title || 'Chưa đặt tên'}</span>
            </h5>
            <button type="button" class="btn btn-danger btn-sm ml-auto btn-delete-quiz" data-item-idx="${quizIdx}">
              <i class="fas fa-trash-alt mr-1"></i> Xóa Quiz này
            </button>
          </div>
          <div class="card-body bg-white">
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Tiêu đề Quiz <span class="text-danger">*</span></label>
                <input type="text" class="form-control quiz-title-input" value="${quiz.title || ''}" placeholder="Nhập tiêu đề Quiz..." data-item-idx="${quizIdx}">
              </div>
              <div class="col-md-6 form-group">
                <label>Mô tả Quiz</label>
                <textarea class="form-control quiz-desc-input" rows="1" placeholder="Mô tả ngắn về bài trắc nghiệm..." data-item-idx="${quizIdx}">${quiz.description || ''}</textarea>
              </div>
            </div>

            <h5 class="mt-4 mb-3 d-flex align-items-center">
              <i class="fas fa-list-ol mr-2 text-primary"></i>Danh sách câu hỏi
              <button type="button" class="btn btn-outline-primary btn-xs ml-auto btn-add-question" data-item-idx="${quizIdx}">
                <i class="fas fa-plus mr-1"></i> Thêm câu hỏi
              </button>
            </h5>

            <div class="questions-container" data-item-idx="${quizIdx}">
      `;

      if (!quiz.questions || quiz.questions.length === 0) {
        quizCardHtml += `
          <div class="text-center py-4 text-muted bg-light border rounded">
            <i class="far fa-comments fa-2x mb-2"></i>
            <p class="mb-0">Chưa có câu hỏi nào. Hãy nhấn <strong>Thêm câu hỏi</strong> để bắt đầu!</p>
          </div>
        `;
      } else {
        quiz.questions.forEach((question, qIdx) => {
          quizCardHtml += `
            <div class="question-card" data-item-idx="${quizIdx}" data-question-idx="${qIdx}">
              <div class="d-flex align-items-center mb-3">
                <span class="badge badge-primary mr-2">Câu ${qIdx + 1}</span>
                <input type="text" class="form-control form-control-sm question-text-input mr-3" value="${question.question_text || ''}" placeholder="Nội dung câu hỏi..." data-item-idx="${quizIdx}" data-question-idx="${qIdx}">
                
                <select class="form-control form-control-sm question-type-select mr-3" style="width: 200px;" data-item-idx="${quizIdx}" data-question-idx="${qIdx}">
                  <option value="single" ${question.type === 'single' ? 'selected' : ''}>Một đáp án đúng (Radio)</option>
                  <option value="multiple" ${question.type === 'multiple' ? 'selected' : ''}>Nhiều đáp án đúng (Checkbox)</option>
                </select>

                <button type="button" class="btn btn-danger btn-xs btn-delete-question" data-item-idx="${quizIdx}" data-question-idx="${qIdx}">
                  <i class="fas fa-times"></i> Xóa câu hỏi
                </button>
              </div>

              <div class="options-container border-top pt-2" data-item-idx="${quizIdx}" data-question-idx="${qIdx}">
                <label class="text-muted small mb-2 d-block">Các phương án lựa chọn (Tích chọn để đánh dấu phương án đúng):</label>
          `;

          if (!question.options || question.options.length === 0) {
            quizCardHtml += `
              <div class="text-muted small mb-2 pl-3">Chưa có phương án nào. Hãy nhấn <strong>Thêm phương án</strong>.</div>
            `;
          } else {
            question.options.forEach((option, oIdx) => {
              const inputType = question.type === 'single' ? 'radio' : 'checkbox';
              const nameAttribute = question.type === 'single' ? `name="correct_opt_${quizIdx}_${qIdx}"` : '';

              quizCardHtml += `
                <div class="option-row">
                  <input type="${inputType}" ${nameAttribute} class="correct-option-input" ${option.is_correct ? 'checked' : ''} data-item-idx="${quizIdx}" data-question-idx="${qIdx}" data-option-idx="${oIdx}">
                  <input type="text" class="form-control form-control-sm option-text-input" value="${option.option_text || ''}" placeholder="Nhập phương án trả lời..." data-item-idx="${quizIdx}" data-question-idx="${qIdx}" data-option-idx="${oIdx}">
                  <i class="fas fa-trash-alt red-icon btn-delete-option" data-item-idx="${quizIdx}" data-question-idx="${qIdx}" data-option-idx="${oIdx}"></i>
                </div>
              `;
            });
          }

          quizCardHtml += `
              </div>
              <button type="button" class="btn btn-outline-success btn-xs mt-2 btn-add-option" data-item-idx="${quizIdx}" data-question-idx="${qIdx}">
                <i class="fas fa-plus mr-1"></i> Thêm phương án
              </button>
            </div>
          `;
        });
      }

      quizCardHtml += `
            </div>
          </div>
        </div>
      `;

      $container.append(quizCardHtml);
      quizIndex++;
    });

    if (quizIndex === 0) {
      $container.html(`
        <div class="text-center py-5 text-muted bg-white rounded border">
          <i class="fas fa-question-circle fa-4x mb-3 text-secondary"></i>
          <p class="lead mb-2">Chưa có bài Quiz nào được tạo</p>
          <p class="text-sm">Hãy nhấn nút <strong>Thêm Quiz mới</strong> để tạo câu hỏi trắc nghiệm kiểm tra cho khóa học.</p>
        </div>
      `);
    }
  }

  // Render Curriculum Tab
  function renderCurriculumTab() {
    const $list = $('#curriculum-list-ui');
    $list.empty();

    if (curriculumList.length === 0) {
      $('#empty-curriculum-msg').removeClass('d-none');
      return;
    } else {
      $('#empty-curriculum-msg').addClass('d-none');
    }

    curriculumList.forEach((item, index) => {
      let badgeHtml = '';
      let titleHtml = '';
      let detailsHtml = '';
      let buttonsHtml = '';
      let thumbHtml = '';

      if (item.type === 'video') {
        thumbHtml = item.epThumbnail 
          ? `<img src="${item.epThumbnail}" class="mr-3 rounded" style="width: 80px; height: 45px; object-fit: cover; border: 1px solid #ddd;" />`
          : `<div class="mr-3 rounded d-flex align-items-center justify-content-center bg-light text-secondary" style="width: 80px; height: 45px; border: 1px dashed #ccc;"><i class="fas fa-video"></i></div>`;

        badgeHtml = `<span class="badge badge-info mr-2"><i class="fas fa-video mr-1"></i> Video</span>`;
        titleHtml = `<strong class="curriculum-item-title">${item.epTitle || 'Chưa đặt tên video'}</strong>`;
        detailsHtml = `<span class="text-muted text-xs d-block mt-1">${item.epDescription ? item.epDescription.substring(0, 100) + '...' : 'Không có mô tả'} (Vimeo ID: ${item.vimeoId})</span>`;
        buttonsHtml = `
          <button type="button" class="btn btn-warning btn-xs btn-edit-video mr-2" data-item-idx="${index}">
            <i class="fas fa-edit"></i> Sửa
          </button>
          <button type="button" class="btn btn-info btn-xs btn-info-video mr-2" video-id="${item.vimeoId}">
            <i class="fas fa-eye"></i> Xem Preview
          </button>
          <button type="button" class="btn btn-danger btn-xs btn-delete-curriculum-item" data-item-idx="${index}">
            <i class="fas fa-trash"></i> Xóa
          </button>
        `;
      } else if (item.type === 'quiz') {
        thumbHtml = '';

        const questionCount = item.questions ? item.questions.length : 0;
        badgeHtml = `<span class="badge badge-warning mr-2"><i class="fas fa-question-circle mr-1"></i> Quiz</span>`;
        titleHtml = `<strong class="curriculum-item-title">${item.title || 'Chưa đặt tên Quiz'}</strong>`;
        detailsHtml = `<span class="text-muted text-xs d-block mt-1">${questionCount} câu hỏi • ${item.description || 'Không có mô tả'}</span>`;
        buttonsHtml = `
          <button type="button" class="btn btn-warning btn-xs btn-edit-quiz-shortcut mr-2" data-item-idx="${index}">
            <i class="fas fa-edit"></i> Sửa Quiz
          </button>
          <button type="button" class="btn btn-danger btn-xs btn-delete-curriculum-item" data-item-idx="${index}">
            <i class="fas fa-trash"></i> Xóa
          </button>
        `;
      }

      const rowHtml = `
        <li class="list-group-item d-flex align-items-center curriculum-item ${item.type === 'quiz' ? 'quiz-type' : ''}" data-item-idx="${index}">
          <i class="fas fa-bars mr-3 handle"></i>
          ${thumbHtml}
          <div class="flex-grow-1">
            <div class="d-flex align-items-center">
              ${badgeHtml}
              ${titleHtml}
            </div>
            ${detailsHtml}
          </div>
          <div class="text-right">
            ${buttonsHtml}
          </div>
        </li>
      `;

      $list.append(rowHtml);
    });
  }

  // --- Event Handlers & State Mutations ---

  // Add Quiz
  $('#btn-add-quiz').on('click', function() {
    curriculumList.push({
      type: 'quiz',
      title: 'Bài trắc nghiệm mới',
      description: '',
      questions: []
    });
    renderAll();
    // Scroll to the bottom of the quiz pane
    $('html, body').animate({
      scrollTop: $(".quiz-card").last().offset().top - 100
    }, 500);
  });

  // Edit Quiz shortcut link from curriculum tab
  $(document).on('click', '.btn-edit-quiz-shortcut', function() {
    const itemIdx = $(this).data('item-idx');
    // Switch to Quiz Tab
    $('#quiz-tab').tab('show');
    // Scroll to the specific quiz card
    setTimeout(() => {
      const $card = $(`.quiz-card[data-item-idx="${itemIdx}"]`);
      if ($card.length) {
        $('html, body').animate({
          scrollTop: $card.offset().top - 100
        }, 500);
        $card.addClass('bg-warning-light');
        setTimeout(() => $card.removeClass('bg-warning-light'), 1500);
      }
    }, 150);
  });

  // Delete Quiz/Video Item
  $(document).on('click', '.btn-delete-curriculum-item', function() {
    const itemIdx = $(this).data('item-idx');
    const item = curriculumList[itemIdx];
    const itemType = item.type === 'video' ? 'Video' : 'Quiz';

    Swal.fire({
      title: `Xác nhận xóa ${itemType}?`,
      text: `Bạn có chắc chắn muốn xóa "${item.epTitle || item.title || 'không tên'}" khỏi chương trình học?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Xác nhận xóa',
      cancelButtonText: "Đóng",
    }).then((result) => {
      if (result.isConfirmed) {
        curriculumList.splice(itemIdx, 1);
        renderAll();
      }
    });
  });

  $(document).on('click', '.btn-delete-quiz', function() {
    const itemIdx = $(this).data('item-idx');
    const item = curriculumList[itemIdx];

    Swal.fire({
      title: `Xác nhận xóa Quiz?`,
      text: `Bạn có chắc chắn muốn xóa bài trắc nghiệm "${item.title || 'không tên'}" này?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Xác nhận xóa',
      cancelButtonText: "Đóng",
    }).then((result) => {
      if (result.isConfirmed) {
        curriculumList.splice(itemIdx, 1);
        renderAll();
      }
    });
  });

  // Edit Quiz Title
  $(document).on('input', '.quiz-title-input', function() {
    const itemIdx = $(this).data('item-idx');
    const titleVal = $(this).val();
    curriculumList[itemIdx].title = titleVal;
    
    // Update header Title of the card immediately
    $(this).closest('.quiz-card').find('.quiz-display-title').text(titleVal || 'Chưa đặt tên');
  });

  // Edit Quiz Description
  $(document).on('input', '.quiz-desc-input', function() {
    const itemIdx = $(this).data('item-idx');
    curriculumList[itemIdx].description = $(this).val();
  });

  // Add Question to Quiz
  $(document).on('click', '.btn-add-question', function() {
    const itemIdx = $(this).data('item-idx');
    if (!curriculumList[itemIdx].questions) {
      curriculumList[itemIdx].questions = [];
    }
    curriculumList[itemIdx].questions.push({
      question_text: '',
      type: 'single',
      options: [
        { option_text: 'Phương án A', is_correct: false },
        { option_text: 'Phương án B', is_correct: false }
      ]
    });
    renderQuizTab();
  });

  // Delete Question
  $(document).on('click', '.btn-delete-question', function() {
    const itemIdx = $(this).data('item-idx');
    const qIdx = $(this).data('question-idx');
    
    curriculumList[itemIdx].questions.splice(qIdx, 1);
    renderQuizTab();
  });

  // Edit Question Text
  $(document).on('input', '.question-text-input', function() {
    const itemIdx = $(this).data('item-idx');
    const qIdx = $(this).data('question-idx');
    curriculumList[itemIdx].questions[qIdx].question_text = $(this).val();
  });

  // Change Question Type
  $(document).on('change', '.question-type-select', function() {
    const itemIdx = $(this).data('item-idx');
    const qIdx = $(this).data('question-idx');
    const newType = $(this).val();
    
    curriculumList[itemIdx].questions[qIdx].type = newType;
    
    // Reset all correctness on type change for safety
    curriculumList[itemIdx].questions[qIdx].options.forEach(opt => {
      opt.is_correct = false;
    });

    renderQuizTab();
  });

  // Add Option to Question
  $(document).on('click', '.btn-add-option', function() {
    const itemIdx = $(this).data('item-idx');
    const qIdx = $(this).data('question-idx');
    
    curriculumList[itemIdx].questions[qIdx].options.push({
      option_text: '',
      is_correct: false
    });
    renderQuizTab();
  });

  // Delete Option
  $(document).on('click', '.btn-delete-option', function() {
    const itemIdx = $(this).data('item-idx');
    const qIdx = $(this).data('question-idx');
    const oIdx = $(this).data('option-idx');
    
    curriculumList[itemIdx].questions[qIdx].options.splice(oIdx, 1);
    renderQuizTab();
  });

  // Edit Option Text
  $(document).on('input', '.option-text-input', function() {
    const itemIdx = $(this).data('item-idx');
    const qIdx = $(this).data('question-idx');
    const oIdx = $(this).data('option-idx');
    
    curriculumList[itemIdx].questions[qIdx].options[oIdx].option_text = $(this).val();
  });

  // Toggle/Select Option Correctness
  $(document).on('change', '.correct-option-input', function() {
    const itemIdx = $(this).data('item-idx');
    const qIdx = $(this).data('question-idx');
    const oIdx = $(this).data('option-idx');
    const isChecked = $(this).prop('checked');
    const question = curriculumList[itemIdx].questions[qIdx];

    if (question.type === 'single') {
      // Set all options of this question to false, then this one to true
      question.options.forEach((opt, idx) => {
        opt.is_correct = (idx === oIdx);
      });
      // Synchronize DOM check states
      $(this).closest('.options-container').find('.correct-option-input').each(function(idx) {
        $(this).prop('checked', idx === oIdx);
      });
    } else {
      // Checkbox multiple choices
      question.options[oIdx].is_correct = isChecked;
    }
  });

  // Sortable curriculum UI Initialization
  $('#curriculum-list-ui').sortable({
    handle: '.handle',
    update: function(event, ui) {
      // Re-order curriculumList state based on new DOM order
      let newCurriculumList = [];
      $('#curriculum-list-ui > li').each(function() {
        const oldIndex = $(this).data('item-idx');
        newCurriculumList.push(curriculumList[oldIndex]);
      });
      curriculumList = newCurriculumList;

      // Update order property of each item to match its new index
      curriculumList.forEach((item, idx) => {
        item.order = idx;
      });

      // Re-render immediately to refresh the data-item-idx parameters
      renderAll();
    }
  });

  // --- Vimeo Video Selection Logic ---
  let table;
  $('#modal-select-video').on('show.bs.modal', function(e) {
    if ($.fn.dataTable.isDataTable('#video-table')) {
      $('#video-table').DataTable().clear().destroy();
    }
    
    // Get list of currently selected vimeo IDs in curriculumList
    const selectedVimeoIds = curriculumList
      .filter(item => item.type === 'video')
      .map(item => item.vimeoId);

    table = $('#video-table').DataTable({
      serverSide: true,
      fixedHeader: true,
      paging: true,
      scrollY: '400px',
      scrollCollapse: true,
      ajax: '/video/anyDataForCreate',
      pageLength: 50,
      order: [],
      columns: [
        {
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
        $(row).data('vimeo-id', data['vimeo_id']);
        $(row).data('vimeo-thumbnail', data['thumbnail_id']);
        $(row).data('duration-seconds', data['duration_seconds']);
        if (selectedVimeoIds.includes(data['vimeo_id'])) {
          $(row).addClass('has-selected');
          $(row).find('.form-checkbox-input').prop('checked', true);
          $(row).find('.form-checkbox-input').attr('disabled', true);
        }
      }
    });
  });

  // Add selected videos from Modal to curriculumList
  $('.btn-select-course-video').on('click', function() {
    $('#video-table .form-checkbox-input:checked:not(:disabled)').each(function() {
      var row = $(this).closest('tr');
      var rowData = table.row(row).data();
      
      curriculumList.push({
        type: 'video',
        epTitle: rowData.title,
        epDescription: '',
        epThumbnail: rowData.thumbnail_id || rowData.videoThumbnail,
        vimeoId: rowData.vimeo_id,
        durationSeconds: rowData.duration_seconds || null
      });
    });

    renderAll();
    $('#modal-select-video').modal('hide');
  });

  // Video Preview Trigger
  $(document).on('click', '.btn-info-video', function(e) {
    e.preventDefault();
    $('.custom-body-content').html('');
    const id = $(this).attr('video-id');
    showVideoDetail(id);
  });

  function showVideoDetail(vimeoId) {
    $.ajax({
      url: '/video/vimeo/detail/' + vimeoId,
      type: 'get',
      success: function(response) {
        if (response.status) {
          $('.custom-body-content').append(response.data);
          $('#exampleModal').modal('show');
          $('.custom-body-content').find('iframe').css({"width": "100%", "height": "500px"});
        } else {
          Swal.fire('fail!', response.message, '');
        }
      }
    });
  }

  $('#exampleModal').on('hidden.bs.modal', function(e) {
    $('.custom-body-content').html('');
  });

  // --- Form Validation and Submission ---
  let isClickedSubmit = false;
  const form = $('#form-course');
  const original = form.serialize();

  $('#form-course').validate({
    ignore: "", // Make sure hidden fields can be validated if required
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
        required: function() {
          CKEDITOR.instances.content.updateElement();
        }
      },
      originalPrice: {
        greaterThan: "#saleOffPrice",
      },
      status: {
        required: true,
      },
      author: {
        maxlength: 255,
      }
    },
    messages: {
      originalPrice: {
        greaterThan: "Giá gốc phải cao hơn hoặc bằng giá sale."
      },
    },
    submitHandler: function(form) {
      // Validate Quiz entries
      let hasValidationError = false;
      curriculumList.forEach((item, index) => {
        if (item.type === 'quiz') {
          if (!item.title || item.title.trim() === '') {
            Swal.fire('Lỗi nhập liệu', `Tiêu đề Quiz tại vị trí #${index + 1} không được để trống!`, 'error');
            hasValidationError = true;
            return false;
          }
          if (item.questions) {
            item.questions.forEach((q, qIdx) => {
              if (!q.question_text || q.question_text.trim() === '') {
                Swal.fire('Lỗi nhập liệu', `Nội dung câu hỏi #${qIdx + 1} trong Quiz "${item.title}" không được để trống!`, 'error');
                hasValidationError = true;
                return false;
              }
              if (!q.options || q.options.length < 2) {
                Swal.fire('Lỗi nhập liệu', `Mỗi câu hỏi trong Quiz "${item.title}" phải có ít nhất 2 phương án lựa chọn!`, 'error');
                hasValidationError = true;
                return false;
              }
              let hasCorrectOption = q.options.some(opt => opt.is_correct);
              if (!hasCorrectOption) {
                Swal.fire('Lỗi nhập liệu', `Câu hỏi #${qIdx + 1} trong Quiz "${item.title}" phải có ít nhất 1 đáp án đúng!`, 'error');
                hasValidationError = true;
                return false;
              }
              q.options.forEach((opt, oIdx) => {
                if (!opt.option_text || opt.option_text.trim() === '') {
                  Swal.fire('Lỗi nhập liệu', `Phương án #${oIdx + 1} trong câu hỏi #${qIdx + 1} của Quiz "${item.title}" không được để trống!`, 'error');
                  hasValidationError = true;
                  return false;
                }
              });
            });
          }
        }
      });

      if (hasValidationError) {
        return false;
      }

      // Serialize prices
      const $originalPrice = $('#originalPrice');
      const $saleOffPrice = $('#saleOffPrice');
      if ($originalPrice.length) $originalPrice.val(sanitizeMoneyToDigits($originalPrice.val()));
      if ($saleOffPrice.length) $saleOffPrice.val(sanitizeMoneyToDigits($saleOffPrice.val()));

      // Build curriculum payload
      $('.input-curriculum-list').val(JSON.stringify(curriculumList));

      // Build fallback video-list legacy format
      let legacyVideoList = [];
      curriculumList.forEach(item => {
        if (item.type === 'video') {
          legacyVideoList.push({
            epTitle: item.epTitle,
            epDescription: item.epDescription,
            vimeoId: item.vimeoId,
            epThumbnail: item.epThumbnail
            ,durationSeconds: item.durationSeconds
          });
        }
      });
      $('.input-video-list').val(JSON.stringify(legacyVideoList));

      form.submit();
    }
  });

  $('.btn-submit-course').on('click', function() {
    isClickedSubmit = true;
    $("#input-pd").fileinput('upload');
    $("#input-banner-pd").fileinput('upload');
  });

  window.onbeforeunload = function() {
    if (form.serialize() != original && !isClickedSubmit) {
      return 'Bạn có chắc chắn muốn rời khỏi trang này? Mọi thay đổi chưa lưu sẽ bị mất.';
    }
  };

  // --- Edit Video Metadata Modal & Upload Handlers ---

  // Trigger Modal
  $(document).on('click', '.btn-edit-video', function() {
    const itemIdx = $(this).data('item-idx');
    const item = curriculumList[itemIdx];

    $('#edit-video-item-idx').val(itemIdx);
    $('#edit-video-title').val(item.epTitle || '');
    $('#edit-video-desc').val(item.epDescription || '');
    $('#edit-video-thumb-url').val(item.epThumbnail || '');
    $('#edit-video-thumb-file').val(''); // Reset file selection

    if (item.epThumbnail) {
      $('#edit-video-thumb-preview').attr('src', item.epThumbnail).show();
    } else {
      $('#edit-video-thumb-preview').hide();
    }

    $('#modal-edit-video-meta').modal('show');
  });

  // Real-time Preview for text input URL
  $('#edit-video-thumb-url').on('input', function() {
    const url = $(this).val();
    if (url) {
      $('#edit-video-thumb-preview').attr('src', url).show();
    } else {
      $('#edit-video-thumb-preview').hide();
    }
  });

  // Handle Video Thumbnail File Upload
  $('#btn-upload-video-thumb').on('click', function() {
    const fileInput = $('#edit-video-thumb-file')[0];
    if (fileInput.files.length === 0) {
      Swal.fire('Lỗi', 'Vui lòng chọn một file ảnh trước!', 'error');
      return;
    }

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('upload', file);
    formData.append('_token', '{{ csrf_token() }}');

    Swal.fire({
      title: 'Đang tải ảnh lên...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    $.ajax({
      url: '/courses/upload-img',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        Swal.close();
        if (response.url) {
          $('#edit-video-thumb-url').val(response.url);
          $('#edit-video-thumb-preview').attr('src', response.url).show();
          Swal.fire('Thành công', 'Tải ảnh lên thành công!', 'success');
        } else {
          Swal.fire('Thất bại', 'Không thể tải ảnh lên, vui lòng thử lại!', 'error');
        }
      },
      error: function() {
        Swal.close();
        Swal.fire('Lỗi', 'Đã xảy ra lỗi trong quá trình tải ảnh lên!', 'error');
      }
    });
  });

  // Save Video Metadata Changes
  $('#btn-save-video-meta').on('click', function() {
    const itemIdx = parseInt($('#edit-video-item-idx').val());
    const title = $('#edit-video-title').val();
    const desc = $('#edit-video-desc').val();
    const thumbUrl = $('#edit-video-thumb-url').val();

    if (!title || title.trim() === '') {
      Swal.fire('Lỗi nhập liệu', 'Tiêu đề video không được để trống!', 'error');
      return;
    }

    curriculumList[itemIdx].epTitle = title;
    curriculumList[itemIdx].epDescription = desc;
    curriculumList[itemIdx].epThumbnail = thumbUrl;

    renderAll();
    $('#modal-edit-video-meta').modal('hide');
    Swal.fire('Thành công', 'Đã lưu thông tin video!', 'success');
  });

  // --- Coupon Management Ajax Handlers ---
  if (course) {
    const courseId = course.id;

    // Load coupons list
    function loadCoupons() {
      $.ajax({
        url: `/courses/${courseId}/coupons`,
        type: 'GET',
        success: function(res) {
          if (res.status) {
            renderCouponsTable(res.data);
          }
        },
        error: function(err) {
          console.error('Failed to fetch coupons:', err);
        }
      });
    }

    // Format money helper
    function formatNumber(num) {
      return new Intl.NumberFormat('vi-VN').format(num);
    }

    // Render Coupons list table
    function renderCouponsTable(coupons) {
      const $tbody = $('#coupon-table-body');
      $tbody.empty();

      if (!coupons || coupons.length === 0) {
        $('#coupon-list-table').hide();
        $('#empty-coupons-msg').removeClass('d-none');
        return;
      }

      $('#coupon-list-table').show();
      $('#empty-coupons-msg').addClass('d-none');

      coupons.forEach(coupon => {
        const discountTypeLabel = coupon.discount_type === 'percent' ? 'Phần trăm (%)' : 'Số tiền cố định (đ)';
        const discountValueLabel = coupon.discount_type === 'percent' ? `${coupon.discount_value}%` : `${formatNumber(coupon.discount_value)}đ`;
        const totalUsesLabel = coupon.max_uses == 0 ? 'Vô hạn' : coupon.max_uses;
        const expirationLabel = coupon.expires_at ? new Date(coupon.expires_at).toLocaleString('vi-VN') : 'Không giới hạn';
        
        // Active status badge & toggle button
        const activeBadgeHtml = coupon.is_active 
          ? `<span class="badge badge-success">Kích hoạt</span>` 
          : `<span class="badge badge-danger">Tạm khóa</span>`;
        const toggleBtnHtml = coupon.is_active
          ? `<button type="button" class="btn btn-outline-warning btn-xs btn-toggle-coupon mr-2" data-id="${coupon.id}" title="Hủy kích hoạt"><i class="fas fa-ban"></i> Tắt</button>`
          : `<button type="button" class="btn btn-outline-success btn-xs btn-toggle-coupon mr-2" data-id="${coupon.id}" title="Kích hoạt"><i class="fas fa-check"></i> Bật</button>`;

        const rowHtml = `
          <tr>
            <td class="font-weight-bold text-primary">${coupon.code}</td>
            <td>${discountTypeLabel}</td>
            <td class="font-weight-bold">${discountValueLabel}</td>
            <td><span class="badge badge-secondary">${coupon.uses}</span> / ${totalUsesLabel}</td>
            <td class="text-sm">${expirationLabel}</td>
            <td class="coupon-status-cell-${coupon.id}">${activeBadgeHtml}</td>
            <td class="text-right">
              <div class="d-inline-flex">
                <span class="coupon-toggle-btn-wrapper-${coupon.id}">${toggleBtnHtml}</span>
                <button type="button" class="btn btn-outline-danger btn-xs btn-delete-coupon" data-id="${coupon.id}" title="Xóa"><i class="fas fa-trash-alt"></i></button>
              </div>
            </td>
          </tr>
        `;
        $tbody.append(rowHtml);
      });
    }

    // Load coupons list on tab show
    $('a[id="coupon-tab"]').on('shown.bs.tab', function (e) {
      loadCoupons();
    });

    // Refresh button
    $('#btn-refresh-coupons').on('click', function() {
      loadCoupons();
    });

    // Generate random coupon code
    $('#btn-generate-coupon-code').on('click', function() {
      const randomCode = 'COUPON-' + Math.random().toString(36).substr(2, 6).toUpperCase();
      $('#coupon-code-input').val(randomCode);
    });

    // Button click create coupon
    $('#btn-submit-coupon').on('click', function(e) {
      e.preventDefault();

      const code = $('#coupon-code-input').val().trim().toUpperCase();
      const discountType = $('#coupon-discount-type').val();
      const discountValue = $('#coupon-discount-value').val();
      const maxUses = $('#coupon-max-uses').val();
      const expiresAt = $('#coupon-expires-at').val();

      if (!code) {
        Swal.fire('Lỗi', 'Vui lòng nhập mã coupon!', 'error');
        return;
      }
      if (!discountValue) {
        Swal.fire('Lỗi', 'Vui lòng nhập giá trị giảm giá!', 'error');
        return;
      }

      Swal.fire({
        title: 'Đang tạo Coupon...',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      $.ajax({
        url: `/courses/${courseId}/coupons`,
        type: 'POST',
        data: {
          code: code,
          discount_type: discountType,
          discount_value: discountValue,
          max_uses: maxUses,
          expires_at: expiresAt ? expiresAt.replace('T', ' ') : null,
          _token: '{{ csrf_token() }}'
        },
        success: function(res) {
          Swal.close();
          if (res.status) {
            Swal.fire('Thành công', res.message, 'success');
            // Reset fields
            $('#coupon-code-input').val('');
            $('#coupon-discount-value').val('');
            $('#coupon-max-uses').val('0');
            $('#coupon-expires-at').val('');
            // Reload list
            loadCoupons();
          } else {
            Swal.fire('Thất bại', res.message, 'error');
          }
        },
        error: function(err) {
          Swal.close();
          const msg = err.responseJSON?.message || 'Có lỗi xảy ra trong quá trình tạo coupon.';
          Swal.fire('Lỗi', msg, 'error');
        }
      });
    });

    // Delete coupon
    $(document).on('click', '.btn-delete-coupon', function() {
      const couponId = $(this).data('id');
      
      Swal.fire({
        title: 'Xác nhận xóa coupon?',
        text: 'Mã giảm giá này sẽ bị xóa vĩnh viễn khỏi khóa học!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý xóa',
        cancelButtonText: 'Hủy'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/courses/coupons/${couponId}`,
            type: 'DELETE',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function(res) {
              if (res.status) {
                Swal.fire('Đã xóa', res.message, 'success');
                loadCoupons();
              } else {
                Swal.fire('Thất bại', res.message, 'error');
              }
            },
            error: function(err) {
              Swal.fire('Lỗi', 'Không thể xóa coupon!', 'error');
            }
          });
        }
      });
    });

    // Toggle coupon active state
    $(document).on('click', '.btn-toggle-coupon', function() {
      const couponId = $(this).data('id');

      $.ajax({
        url: `/courses/coupons/${couponId}/toggle-active`,
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function(res) {
          if (res.status) {
            Swal.fire('Thành công', res.message, 'success');
            loadCoupons();
          } else {
            Swal.fire('Thất bại', res.message, 'error');
          }
        },
        error: function(err) {
          Swal.fire('Lỗi', 'Không thể thay đổi trạng thái coupon!', 'error');
        }
      });
    });
  }

  // Initial render on page load
  renderAll();
});
</script>
