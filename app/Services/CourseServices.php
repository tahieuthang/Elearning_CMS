<?php

namespace App\Services;

use App\Models\Customer;
use App\Helpers\Helper;
use App\Models\Course;
use App\Models\CourseVideo;
use App\Models\CourseCategoryPivot;
use App\Models\CourseTag;
use App\Models\HotContent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PostCategory;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CourseServices
{
  public function formatCourseDatatables($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('courseStatus', function ($row) {
        return __('course.status_list')[$row->status];
      })
      ->addColumn('courseThumbnail', function ($row) {
        if (!empty($row->thumbnail)) {
          return '<img style="width: 120px" src="' . $row->thumbnail . '" />';
        }
        return '<img style="width: 120px" src="' . url('/images/default_image.png') . '" />';
      })
      ->addColumn('courseBanner', function ($row) {
        return '<img style="width: 120px" src="' . $row->banner . '" />';
      })
      ->addColumn('originalPrice', function ($row) {
        return Helper::convertMoney($row->original_price);
      })
      ->addColumn('saleOffPrice', function ($row) {
        return Helper::convertMoney($row->sale_off_price);
      })
      ->addColumn('courseCategory', function ($row) {
        if ($row->courseCategories) {
          $html = '';
          foreach ($row->courseCategories as $category) {
            $html .= '<span class="badge bg-success mr-1">' . $category->category_name . '</span>';
          }
          return $html;
        }
        return '';
      })
      ->editColumn('created_at', function ($row) {
        return $row->created_at ? Carbon::parse($row->created_at)->format('H:i:s d/m/Y') : '';
      })
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('course.edit')) {
          $action .= '<a href="/courses/detail/' . $row->id . '" class="edit btn btn-primary btn-sm btn-action-icon mr-1" title="' . e(__('course.detail_course')) . '" aria-label="' . e(__('course.detail_course')) . '" data-toggle="tooltip"><i class="fas fa-eye"></i></a>';
        }
        if (Helper::checkPermission('course.delete')) {
          $action .= '<button type="button" data-id="' . $row->id . '" data-name="' . e($row->title) . '" class="btn-delete-course btn btn-danger btn-sm btn-action-icon" title="' . e(__('course.delete_course')) . '" aria-label="' . e(__('course.delete_course')) . '" data-toggle="tooltip"><i class="fas fa-trash"></i></button>';
        }
        return $action;
      })
      ->rawColumns(['action', 'courseCategory', 'courseThumbnail', 'courseBanner'])
      ->make(true);
  }

  public function formatHotCourseDatatables($data)
  {
    return Datatables::of($data)
      ->addColumn('check', function ($row) {
        return '<input type="checkbox" class="form-checkbox-input">';
      })
      ->addIndexColumn()
      ->addColumn('courseStatus', function ($row) {
        return __('course.status_list')[$row->status];
      })
      ->addColumn('courseThumbnail', function ($row) {
        if (!empty($row->thumbnail)) {
          return '<img style="width: 120px" src="' . $row->thumbnail . '" />';
        }
        return '<img style="width: 120px" src="' . url('/images/default_image.png') . '" />';
      })
      ->addColumn('courseBanner', function ($row) {
        return '<img style="width: 120px" src="' . $row->banner . '" />';
      })
      ->addColumn('originalPrice', function ($row) {
        return Helper::convertMoney($row->original_price);
      })
      ->addColumn('saleOffPrice', function ($row) {
        return Helper::convertMoney($row->sale_off_price);
      })
      ->addColumn('courseCategory', function ($row) {
        if ($row->courseCategories) {
          $html = '';
          foreach ($row->courseCategories as $category) {
            $html .= '<span class="badge bg-success mr-1">' . $category->category_name . '</span>';
          }
          return $html;
        }
        return '';
      })
      ->addColumn('action', function ($row) {
        $action = '';
        if (Helper::checkPermission('course.edit')) {
          $action .= '<a href="/courses/detail/' . $row->id . '" class="edit btn btn-primary btn-sm btn-action-icon mr-1" title="' . e(__('course.detail_course')) . '" aria-label="' . e(__('course.detail_course')) . '" data-toggle="tooltip"><i class="fas fa-eye"></i></a>';
        }
        if (Helper::checkPermission('course.delete')) {
          $action .= '<button type="button" data-id="' . $row->id . '" data-name="' . e($row->title) . '" class="btn-delete-course btn btn-danger btn-sm btn-action-icon" title="' . e(__('course.delete_course')) . '" aria-label="' . e(__('course.delete_course')) . '" data-toggle="tooltip"><i class="fas fa-trash"></i></button>';
        }
        return $action;
      })
      ->rawColumns(['check', 'action', 'courseCategory', 'courseThumbnail', 'courseBanner'])
      ->make(true);
  }

  public function processUploadImage($image)
  {
    return $this->processSaveFileToStorage($image);
  }

  public function processSaveFileToStorage($image)
  {
    $fileImage = time() . '_' . $image->getClientOriginalName();
    $image->move(public_path('uploads'), $fileImage);
    return '/uploads/' . $fileImage;
  }

  public function createHotCourseList($courseList)
  {
    DB::beginTransaction();
    try {
      $hotContentData = [];
      foreach ($courseList as $course) {
        $course = Course::find($course['id']);
        if ($course) {
          $courseId = $course['id'];
          $hotContentData[] = [
            'content_type' => 'course',
            'content_id' => $courseId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
          ];
        }
      }
      if (!empty($hotContentData)) {
        HotContent::insert($hotContentData);
      }
      DB::commit();
      $courseList = HotContent::where('content_type', 'course')->with('course')->get();
      return [
        'status' => true,
        'courseList' => $courseList,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processCreateCourse($formData)
  {
    try {
      $imageThumbnailUrl = [];
      if (isset($formData['input-pd']) && count($formData['input-pd']) > 0) {
        foreach ($formData['input-pd'] as $thumbnail) {
          $imageThumbnailUrl[] = $this->processSaveFileToStorage($thumbnail);
        }
      }
      $imageBannerUrl = [];
      if (isset($formData['input-banner-pd']) && count($formData['input-banner-pd']) > 0) {
        foreach ($formData['input-banner-pd'] as $banner) {
          $imageBannerUrl[] = $this->processSaveFileToStorage($banner);
        }
      }
      $courseData = [
        'title' => $formData['title'],
        'description' => $formData['description'],
        'thumbnail' => isset($imageThumbnailUrl[0]) ? $imageThumbnailUrl[0] : null,
        'banner' => isset($imageBannerUrl[0]) ? $imageBannerUrl[0] : null,
        'author' => $formData['author'],
        'authorDescription' => $formData['authorDescription'],
        'course_duration' => $formData['courseDuration'],
        'content' => $formData['content'],
        'status' => $formData['status'],
        'original_price' => $formData['originalPrice'] ? Helper::convertMoneyToNumber($formData['originalPrice']) : 0,
        'sale_off_price' => $formData['saleOffPrice'] ? Helper::convertMoneyToNumber($formData['saleOffPrice']) : 0,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now()
      ];
      // dd($postData);
      DB::beginTransaction();
      $courseId = Course::insertGetId($courseData);
      if (isset($formData['courseCategories'])) {
        $dataCategories = [];
        foreach ($formData['courseCategories'] as $category) {
          $dataCategories[] = [
            'course_id' => $courseId,
            'post_category_id' => $category
          ];
          // dd($dataCategories);
          CourseCategoryPivot::insert($dataCategories);
        }
      }

      if (isset($formData['courseTags'])) {
        $dataTags = [];
        foreach ($formData['courseTags'] as $tag) {
          $dataTags[] = [
            'course_id' => $courseId,
            'tag_id' => $tag
          ];
          CourseTag::insert($dataTags);
        }
      }
      if (!empty($formData['curriculum-list'])) {
        $curriculumList = json_decode($formData['curriculum-list'], true);
        if (!empty($curriculumList)) {
          foreach ($curriculumList as $index => $item) {
            if ($item['type'] === 'video') {
              CourseVideo::create([
                'course_id' => $courseId,
                'video_title' => $item['epTitle'] ?? '',
                'video_description' => $item['epDescription'] ?? '',
                'vimeo_id' => $item['vimeoId'] ?? 0,
                'video_thumbnail' => $item['epThumbnail'] ?? '',
                'duration_seconds' => !empty($item['durationSeconds']) ? (int) $item['durationSeconds'] : null,
                'order' => $index,
              ]);
            } elseif ($item['type'] === 'quiz') {
              $quiz = \App\Models\Quiz::create([
                'course_id' => $courseId,
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'order' => $index,
              ]);
              if (!empty($item['questions'])) {
                foreach ($item['questions'] as $qIndex => $qItem) {
                  $question = \App\Models\QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $qItem['question_text'] ?? '',
                    'type' => $qItem['type'] ?? 'single',
                    'order' => $qIndex,
                  ]);
                  if (!empty($qItem['options'])) {
                    foreach ($qItem['options'] as $oItem) {
                      \App\Models\QuizOption::create([
                        'question_id' => $question->id,
                        'option_text' => $oItem['option_text'] ?? '',
                        'is_correct' => filter_var($oItem['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                      ]);
                    }
                  }
                }
              }
            }
          }
        }
      } elseif (!empty($formData['video-list'])) {
        $videoDataToSave = [];
        $videoList = json_decode($formData['video-list']);
        if (!empty($videoList)) {
          foreach ($videoList as $video) {
            $videoDataToSave[] = [
              'course_id' => $courseId,
              'video_title' => $video->epTitle,
              'video_description' => $video->epDescription,
              'vimeo_id' => $video->vimeoId,
              'video_thumbnail' => $video->epThumbnail,
              'duration_seconds' => !empty($video->durationSeconds) ? (int) $video->durationSeconds : null,
              'created_at' => Carbon::now()->toDateTimeString(),
              'created_at' => Carbon::now()->toDateTimeString(),
            ];
          }
        }
        CourseVideo::insert($videoDataToSave);
      }

      DB::commit();
      return [
        'status' => true,
        'message' => 'success',
        'id' => $courseId
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processUpdateCourse($id, $formData)
  {
    try {
      $thumbnailUrls = [];
      if (isset($formData['input-pd']) && count($formData['input-pd']) > 0) {
        foreach ($formData['input-pd'] as $thumbnail) {
          $thumbnailUrls[] = $this->processSaveFileToStorage($thumbnail);
        }
      }
      $bannerUrls = [];
      if (isset($formData['input-banner-pd']) && count($formData['input-banner-pd']) > 0) {
        foreach ($formData['input-banner-pd'] as $banner) {
          $bannerUrls[] = $this->processSaveFileToStorage($banner);
        }
      }
      $currentData = Course::find($id);
      $courseData = [
        'title' => isset($formData['title']) ? $formData['title'] : $currentData->title,
        'description' => isset($formData['description']) ? $formData['description'] : $currentData->description,
        'author' => isset($formData['author']) ? $formData['author'] : $currentData->author,
        'authorDescription' => isset($formData['authorDescription']) ? $formData['authorDescription'] : $currentData->authorDescription,
        'course_duration' => isset($formData['courseDuration']) ? $formData['courseDuration'] : $currentData->course_duration,
        'content' => isset($formData['content']) ? $formData['content'] : $currentData->content,
        'status' => isset($formData['status']) ? $formData['status'] : $currentData->status,
        'original_price' => isset($formData['originalPrice']) ? Helper::convertMoneyToNumber($formData['originalPrice']) : $currentData->original_price,
        'sale_off_price' => isset($formData['saleOffPrice']) ? Helper::convertMoneyToNumber($formData['saleOffPrice']) : $currentData->sale_off_price,
        'updated_at' => Carbon::now()
      ];

      if (count($thumbnailUrls) > 0) {
        $courseData['thumbnail'] = $thumbnailUrls[0];
      }

      if (count($bannerUrls) > 0) {
        $courseData['banner'] = $bannerUrls[0];
      }
      DB::beginTransaction();

      Course::where('id', $id)->update($courseData);

      if (isset($formData['courseCategories'])) {
        $dataCategories = [];
        foreach ($formData['courseCategories'] as $category) {
          $dataCategories[] = [
            'course_id' => $id,
            'post_category_id' => $category
          ];
          // dd($dataCategories);
          CourseCategoryPivot::where('course_id', $id)->delete();
          CourseCategoryPivot::insert($dataCategories);
        }
      }

      if (isset($formData['courseTags'])) {
        $dataTags = [];
        foreach ($formData['courseTags'] as $tag) {
          $dataTags[] = [
            'course_id' => $id,
            'tag_id' => $tag
          ];
          CourseTag::insert($dataTags);
        }
      }

      CourseVideo::where('course_id', $id)->delete();
      $existingQuizIds = \App\Models\Quiz::where('course_id', $id)->pluck('id');
      if ($existingQuizIds->isNotEmpty()) {
        \App\Models\QuizOption::whereIn('question_id', function ($q) use ($existingQuizIds) {
          $q->select('id')->from('quiz_questions')->whereIn('quiz_id', $existingQuizIds);
        })->delete();
        \App\Models\QuizQuestion::whereIn('quiz_id', $existingQuizIds)->delete();
        \App\Models\Quiz::where('course_id', $id)->delete();
      }

      if (!empty($formData['curriculum-list'])) {
        $curriculumList = json_decode($formData['curriculum-list'], true);
        if (!empty($curriculumList)) {
          foreach ($curriculumList as $index => $item) {
            if ($item['type'] === 'video') {
              CourseVideo::create([
                'course_id' => $id,
                'video_title' => $item['epTitle'] ?? '',
                'video_description' => $item['epDescription'] ?? '',
                'vimeo_id' => $item['vimeoId'] ?? 0,
                'video_thumbnail' => $item['epThumbnail'] ?? '',
                'duration_seconds' => !empty($item['durationSeconds']) ? (int) $item['durationSeconds'] : null,
                'order' => $index,
              ]);
            } elseif ($item['type'] === 'quiz') {
              $quiz = \App\Models\Quiz::create([
                'course_id' => $id,
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'order' => $index,
              ]);
              if (!empty($item['questions'])) {
                foreach ($item['questions'] as $qIndex => $qItem) {
                  $question = \App\Models\QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $qItem['question_text'] ?? '',
                    'type' => $qItem['type'] ?? 'single',
                    'order' => $qIndex,
                  ]);
                  if (!empty($qItem['options'])) {
                    foreach ($qItem['options'] as $oItem) {
                      \App\Models\QuizOption::create([
                        'question_id' => $question->id,
                        'option_text' => $oItem['option_text'] ?? '',
                        'is_correct' => filter_var($oItem['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                      ]);
                    }
                  }
                }
              }
            }
          }
        }
      } elseif (!empty($formData['video-list'])) {
        $courseVideoData = [];
        $videoList = json_decode($formData['video-list']);
        if (!empty($videoList)) {
          foreach ($videoList as $video) {
            $courseVideoData[] = [
              'course_id' => $id,
              'video_title' => $video->epTitle,
              'video_description' => $video->epDescription,
              'vimeo_id' => $video->vimeoId,
              'video_thumbnail' => $video->epThumbnail,
              'duration_seconds' => !empty($video->durationSeconds) ? (int) $video->durationSeconds : null,
              'updated_at' => Carbon::now()->toDateTimeString(),
            ];
          }
          CourseVideo::insert($courseVideoData);
        }
      }
      DB::commit();
      return [
        'status' => true,
        'message' => 'success',
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function deleteFileFromStorage($filePath)
  {
    $fullPath = public_path($filePath);

    if (file_exists($fullPath)) {
      unlink($fullPath);
    } else {
      Log::error("File not found: " . $fullPath);
    }
  }

  public function processDeleteImage($id)
  {
    DB::beginTransaction();
    try {
      $course = Course::find($id);
      if ($course && $course->thumbnail) {
        $oldThumbnail = $course->thumbnail;
        $course->thumbnail = null;
        $this->deleteFileFromStorage($oldThumbnail);
      }
      // if ($course && $course->banner) {
      //   $oldBanner = $course->banner;
      //   $course->banner = null;
      //   $this->deleteFileFromStorage($oldBanner);
      // }
      $course->save();
      DB::commit();
      return [
        'status' => true,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }
  public function processDeleteBannerImage() {}
  public function deleteLocalPublicFile() {}
  public function processDeleteCourse($courseId)
  {
    try {
      DB::beginTransaction();
      $course = Course::find($courseId);
      $thumbnail = $course->thumbnail;
      $banner = $course->banner;
      $course->courseTags()->detach();
      $course->courseCategories()->detach();
      $course->videos()->delete();
      $course->delete();
      DB::commit();
      if ($thumbnail) {
        $this->deleteFileFromStorage($thumbnail);
      }
      if ($banner) {
        $this->deleteFileFromStorage($banner);
      }
      return [
        'status' => true,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processDeleteCourseHot($id)
  {
    DB::beginTransaction();
    try {
      $course = HotContent::where('content_id', $id)->first();
      if (!$course) {
        return [
          'status' => false,
          'message' => __('course.message.course_not_found')
        ];
      }
      $course->delete();
      DB::commit();
      return [
        'status' => true,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      DB::rollback();
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processUploadImageToS3() {}

  public function getCourses($filterData)
  {
    $queries = Course::with(['courseCategories', 'courseTags']);
    if (isset($filterData['courseCategories']) && count($filterData['courseCategories']) > 0) {
      $queries->whereHas('courseCategories', function ($q) use ($filterData) {
        return $q->whereIn('post_categories.id', $filterData['courseCategories']);
      });
    }

    if (isset($filterData['tags']) && count($filterData['tags']) > 0) {
      $queries->whereHas('courseTags', function ($q) use ($filterData) {
        return $q->whereIn('tags.id', $filterData['tags']);
      });
    }

    if (isset($filterData['statusList']) && count($filterData['statusList']) > 0) {
      $queries->whereIn('status', $filterData['statusList']);
    }

    if (isset($filterData['keyword'])) {
      $queries->where(function ($q) use ($filterData) {
        $likeStr = '%' . Helper::escapeLike($filterData['keyword']) . '%';
        $q->where('courses.title', 'like', $likeStr)
          ->orWhere('courses.description', 'like', $likeStr)
          ->orWhere('courses.content', 'like', $likeStr);
      });
    }
    return $queries;
  }

  public function getCoursesList($filterData)
  {
    // Check if rating columns exist (migration may not have run yet)
    $hasRatingColumns = Schema::hasColumn('courses', 'rating_average') && Schema::hasColumn('courses', 'rating_count');

    $selectColumns = [
      "id",
      "title",
      "description",
      "thumbnail",
      "banner",
      "author",
      "authorDescription",
      "course_duration",
      "content",
      "original_price",
      "sale_off_price",
    ];

    // Add rating columns only if they exist
    if ($hasRatingColumns) {
      $selectColumns[] = "rating_average";
      $selectColumns[] = "rating_count";
    }

    $results = Course::select($selectColumns)->with([
      'courseCategories:id,category_name',
      'courseTags:id,tag_name'
    ]);
    $perPage = !empty($filterData['per_page']) ? $filterData['per_page'] : config('constants.per_page');
    $page = !empty($filterData['page']) ? $filterData['page'] : config('constants.page');

    $customerInfo = auth('customer')->user();
    $myCourses = filter_var($filterData['my_courses'] ?? false, FILTER_VALIDATE_BOOLEAN);
    if ($myCourses && $customerInfo) {
      $purchasedCourseIds = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.customer_id', $customerInfo->id)
        ->where('orders.status', config('constants.order_status.completed'))
        ->pluck('course_id')
        ->toArray();

      $progressCourseIds = \App\Models\CustomerCourseProgress::where('customer_id', $customerInfo->id)
        ->pluck('course_id')
        ->toArray();

      $myCourseIds = array_unique(array_merge($purchasedCourseIds, $progressCourseIds));
      $results->whereIn('courses.id', $myCourseIds);
    }

    if (!empty($filterData['status'])) {
      $results->whereIn('status', $filterData['status']);
    }

    if (!empty($filterData['category_name'])) {
      $results->whereHas('courseCategories', function ($q) use ($filterData) {
        $q->whereIn('category_name', $filterData['category_name']);
      });
    }

    if (!empty($filterData['tag_name'])) {
      $results->whereHas('courseTags', function ($q) use ($filterData) {
        $q->whereIn('tag_name', $filterData['tag_name']);
      });
    }

    if (!empty($filterData['rating_average'])) {
      $results->where('rating_average', '>=', $filterData['rating_average']);
    }

    if (!empty($filterData['sort_by'])) {
      if ($filterData['sort_by'] === 'price-low') {
        $results->orderBy('sale_off_price', 'asc');
      } elseif ($filterData['sort_by'] === 'price-high') {
        $results->orderBy('sale_off_price', 'desc');
      } elseif ($filterData['sort_by'] === 'rating') {
        $results->orderBy('rating_average', 'desc');
      }
    }

    if (isset($filterData['keyword'])) {
      $results->where(function ($q) use ($filterData) {
        $likeStr = '%' . Helper::escapeLike($filterData['keyword']) . '%';
        $q->where('courses.title', 'like', $likeStr)
          ->orWhere('courses.description', 'like', $likeStr)
          ->orWhere('courses.content', 'like', $likeStr);
      });
    }
    return $results->paginate($perPage, ['*'], '', $page);
  }

  // Hàm này giúp render ra all course, course nào đã dc mua sẽ đc format lại bằng is_bought = true
  // và ẩn ivdeo của course chưa đc mua
  public function formatCourseData($courseList)
  {
    $customerInfo = auth('customer')->user();
    $courseData = [];
    $completedQuizIds = [];

    if (!empty($customerInfo)) {
      $orderByUserList = Order::where([
        ['customer_id', $customerInfo->id],
        ['status', config('constants.order_status.completed')],
      ])->with([
        'courses' => function ($q) {
          $q->where('status', config('constants.course_status_by_text.active'));
        }
      ])->get()->toArray();

      foreach ($orderByUserList as $orderByUser) {
        if (empty($orderByUser['courses'])) {
          continue;
        }
        foreach ($orderByUser['courses'] as $courseItem) {
          $courseData[] = $courseItem['id'];
        }
      }

      $completedQuizIds = \App\Models\CustomerQuiz::where('customer_id', $customerInfo->id)
        ->where('is_passed', true)
        ->pluck('quiz_id')
        ->toArray();
    }

    // 1. Optimize N+1 query: fetch all course progress in one query
    $courseIds = [];
    foreach ($courseList as $course) {
      $courseIds[] = $course->id;
    }

    $progressData = [];
    if ($customerInfo && !empty($courseIds)) {
      $progressData = \App\Models\CustomerCourseProgress::where('customer_id', $customerInfo->id)
        ->whereIn('course_id', $courseIds)
        ->pluck('progress_percent', 'course_id')
        ->toArray();
    }

    // 2. Optimize curriculum processing
    foreach ($courseList as &$course) {
      $course->is_bought = in_array($course->id, $courseData);
      $isFree = ($course->original_price == 0 && $course->sale_off_price == 0) || ($course->original_price == $course->sale_off_price);
      $hasAccess = $course->is_bought || $isFree;

      // Only format videos if they are eager loaded
      if ($course->relationLoaded('videos')) {
        if (!$hasAccess) {
          $course->videos = $this->__removeVideo($course->videos, true);
        }
      }

      // Only format quizzes if they are eager loaded
      if ($course->relationLoaded('quizzes')) {
        if ($course->quizzes) {
          foreach ($course->quizzes as $quiz) {
            if ($quiz->relationLoaded('questions')) {
              foreach ($quiz->questions as $question) {
                if ($question->relationLoaded('options')) {
                  foreach ($question->options as $option) {
                    $option->makeHidden('is_correct');
                  }
                }
              }
            }
          }
        }
      }

      // Apply the pre-fetched progress percent
      $course->progress_percent = $progressData[$course->id] ?? 0;

      // Only build curriculum if either videos or quizzes are eager loaded
      if ($course->relationLoaded('videos') || $course->relationLoaded('quizzes')) {
        $videos = $course->relationLoaded('videos') ? ($course->videos ?? collect()) : collect();
        $quizzes = $course->relationLoaded('quizzes') ? ($course->quizzes ?? collect()) : collect();
        $curriculum = collect();
        foreach ($videos as $video) {
          $video->type = 'video';
          $curriculum->push($video);
        }
        foreach ($quizzes as $quiz) {
          $quiz->type = 'quiz';
          $curriculum->push($quiz);
        }
        $course->curriculum = $curriculum->sortBy('order')->values()->all();
      } else {
        // If not loaded, clean up dynamic relations to reduce payload size
        $course->unsetRelation('videos');
        $course->unsetRelation('quizzes');
      }
    }

    return $courseList;
  }

  private function __removeVideo($videoData, $hideVideo)
  {
    if (!empty($videoData) && $hideVideo) {
      foreach ($videoData as $video) {
        $video->vimeo_id = '';
      }
    }
    return $videoData;
  }

  public function getCourseById($id)
  {
    $courseDetail = Course::select([
      'id',
      'title',
      'description',
      'thumbnail',
      'banner',
      'author',
      'authorDescription',
      'course_duration',
      'content',
      'status',
      'original_price',
      'sale_off_price'
    ])
      ->with([
        'courseCategories:id,category_name',
        'courseTags:id,tag_name',
        'videos',
        'quizzes.questions.options'
      ])
      ->where('id', $id)
      ->first();
    if (!$courseDetail) {
      throw new NotFoundHttpException(__('message.not_found_course', [], 'Course not found'));
    }

    $customerInfo = auth('customer')->user();
    $courseDetail->progress_percent = 0;

    if ($customerInfo) {
      $isBought = Order::where([
        ['customer_id', $customerInfo->id],
        ['status', config('constants.order_status.completed')]
      ])
        ->whereHas('courses', function ($q) use ($id) {
          $q->where('courses.id', $id)
            ->where('status', config('constants.course_status_by_text.active'));
        })
        ->exists();
      if ($isBought) {
        $courseDetail->is_bought = $isBought;
      }

      $courseProgress = \App\Models\CustomerCourseProgress::where('customer_id', $customerInfo->id)
        ->where('course_id', $id)
        ->first();
      $courseDetail->progress_percent = $courseProgress ? $courseProgress->progress_percent : 0;
    }

    $isFree = ($courseDetail->original_price == 0 && $courseDetail->sale_off_price == 0) || ($courseDetail->original_price == $courseDetail->sale_off_price);
    $hasAccess = $courseDetail->is_bought || $isFree;

    if (!$hasAccess) {
      $courseDetail->videos = $this->__removeVideo($courseDetail->videos ?? [], true);
    }

    $completedVideoIds = [];
    $completedQuizIds = [];
    if ($customerInfo) {
      $completedVideoIds = \App\Models\CustomerVideoProgress::where('customer_id', $customerInfo->id)
        ->where('course_id', $id)
        ->where('is_completed', true)
        ->pluck('course_video_id')
        ->toArray();

      $completedQuizIdsLegacy = \App\Models\CustomerQuiz::where('customer_id', $customerInfo->id)
        ->where('is_passed', true)
        ->pluck('quiz_id')
        ->toArray();

      $completedQuizIdsAdvanced = \App\Models\CustomerQuizProgress::where('customer_id', $customerInfo->id)
        ->where('course_id', $id)
        ->where('is_completed', true)
        ->pluck('quiz_id')
        ->toArray();

      $completedQuizIds = array_unique(array_merge($completedQuizIdsLegacy, $completedQuizIdsAdvanced));
    }

    // Build merged, ordered curriculum
    $videos = $courseDetail->videos ?? collect();
    $quizzes = $courseDetail->quizzes ?? collect();
    $curriculum = collect();
    foreach ($videos as $video) {
      $video->type = 'video';
      $video->is_completed = in_array($video->id, $completedVideoIds);
      $curriculum->push($video);
    }
    foreach ($quizzes as $quiz) {
      $quiz->type = 'quiz';
      $quiz->is_passed = in_array($quiz->id, $completedQuizIds);
      $quiz->is_completed = in_array($quiz->id, $completedQuizIds);
      if ($quiz->questions) {
        foreach ($quiz->questions as $question) {
          if ($question->options) {
            foreach ($question->options as $option) {
              $option->makeHidden('is_correct');
            }
          }
        }
      }
      $curriculum->push($quiz);
    }
    $courseDetail->curriculum = $curriculum->sortBy('order')->values()->all();

    return $courseDetail;
  }

  public function getCourseTop()
  {
    try {
      $courseList = HotContent::where('content_type', 'course')->with('course')->get();
      return [
        'status' => true,
        'courseList' => $courseList,
        'message' => 'success'
      ];
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function getReviewByCourse($id, $request)
  {
    try {
      $page = !empty($request->page) ? $request->page : config('constants.page');
      $perPage = !empty($request->per_page) ? $request->per_page : config('constants.per_page');

      $listReviewByCourse = Review::with(['customer', 'course'])->where('course_id', $id);
      $countReviews = Review::where('course_id', $id)->count();

      $numberRate = range(1, 5);
      $rawRateData = DB::table('reviews')->select('rate', DB::raw('count(*) as total'))->where('course_id', $id)
        ->groupBy('rate')->orderBy('rate', 'desc')->pluck('total', 'rate');
      $rateData = collect();
      $totalRate = 0;
      $totalPoint = 0;

      foreach ($numberRate as $value) {
        $total = $rawRateData[$value] ?? 0;
        $percent = $countReviews > 0 ? round(($total / $countReviews) * 100, 2) : 0;
        $rateData->push((object)[
          'rate' => $value,
          'total' => $total,
          'percent' => $percent,
        ]);
        $totalRate += $total;
        $totalPoint += $value * $total;
      };

      $averageRatePoint = $totalRate > 0 ? round($totalPoint / $totalRate, 2) : 0;
      return [
        'list' => $listReviewByCourse->paginate($perPage, ['*'], '', $page),
        'rate' => $rateData,
        'average' => $averageRatePoint
      ];
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function addReviews($data)
  {
    $user = auth('customer')->user();
    if (!$user) {
      throw new \Exception('Unauthenticated customer');
    }
    $userId = $user->id;
    $isBought = Order::where([
      ['customer_id', $userId],
      ['status', config('constants.order_status.completed')]
    ])
      ->whereHas('courses', function ($q) use ($data) {
        $q->where('courses.id', $data['course_id'])
          ->where('status', config('constants.course_status_by_text.active'));;
      })->exists();
    if (!$isBought) {
      throw new \Exception('Course not purchased');
    }
    $review = Review::create([
      'course_id' => $data['course_id'],
      'customer_id' => $userId,
      'comment' => $data['comment'],
      'rate' => $data['rate']
    ]);

    return $review;
  }

  public function getCategoryBestOfUser()
  {
    try {
      $customerId = auth('customer')->user()->id;
      $courseData = Course::whereHas('orders', function ($q) use ($customerId) {
        $q->where('status', 3)->where('customer_id', $customerId);
      })->get();
      $categoryCount = collect();
      foreach ($courseData as $course) {
        foreach ($course->courseCategories as $category) {
          $categoryId = $category->id;
          if ($categoryCount->has($categoryId)) {
            $categoryCount[$categoryId]++;
          } else {
            $categoryCount[$categoryId] = 1;
          }
        }
      }
      $maxCount = $categoryCount->max();
      $categoryIdWithMaxCount = $categoryCount->search($maxCount);
      return $categoryIdWithMaxCount;
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function getNewCourses($id)
  {
    try {
      $courseNew = Course::with('courseCategories')
        ->whereHas('courseCategories', function ($q) use ($id) {
          $q->where('post_categories.id', $id);
        })
        ->where('created_at', '>=', now()->subDays(3))
        ->latest()
        ->take(5)
        ->get();
      return $courseNew;
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  /**
   * Update the overall course progress percentage for a customer.
   */
  public function updateCourseProgress($customerId, $courseId)
  {
    $totalVideos = CourseVideo::where('course_id', $courseId)->count();
    $totalQuizzes = \App\Models\Quiz::where('course_id', $courseId)->count();
    $totalItems = $totalVideos + $totalQuizzes;

    if ($totalItems === 0) {
      $progressPercent = 0;
    } else {
      $completedVideos = \App\Models\CustomerVideoProgress::where('customer_id', $customerId)
        ->where('course_id', $courseId)
        ->where('is_completed', true)
        ->count();

      $quizIds = \App\Models\Quiz::where('course_id', $courseId)->pluck('id')->toArray();

      $completedQuizIdsLegacy = \App\Models\CustomerQuiz::where('customer_id', $customerId)
        ->whereIn('quiz_id', $quizIds)
        ->where('is_passed', true)
        ->pluck('quiz_id')
        ->toArray();

      $completedQuizIdsAdvanced = \App\Models\CustomerQuizProgress::where('customer_id', $customerId)
        ->where('course_id', $courseId)
        ->where('is_completed', true)
        ->pluck('quiz_id')
        ->toArray();

      $completedQuizzes = count(array_unique(array_merge($completedQuizIdsLegacy, $completedQuizIdsAdvanced)));

      $progressPercent = (int) round((($completedVideos + $completedQuizzes) / $totalItems) * 100);
      $progressPercent = min(100, max(0, $progressPercent));
    }

    \App\Models\CustomerCourseProgress::updateOrCreate(
      [
        'customer_id' => $customerId,
        'course_id' => $courseId,
      ],
      [
        'progress_percent' => $progressPercent,
      ]
    );

    return $progressPercent;
  }

  /**
   * Save progress for a video and update overall course progress.
   */
  public function trackVideoProgress($customerId, $courseVideoId, $watchedSeconds, $totalSeconds, $isCompletedInput = null, array $watchedRanges = [])
  {
    $courseVideo = CourseVideo::findOrFail($courseVideoId);
    $courseId = $courseVideo->course_id;

    $progress = \App\Models\CustomerVideoProgress::firstOrNew([
      'customer_id' => $customerId,
      'course_video_id' => $courseVideoId,
    ]);

    $progress->course_id = $courseId;
    $progress->total_seconds = max($progress->total_seconds, $totalSeconds);

    if ($watchedRanges !== []) {
      $existingRanges = $progress->watched_ranges ?? [];
      if ($existingRanges === [] && $progress->watched_seconds > 0) {
        // Historical rows have no range information. Preserve their scalar
        // total as a conservative baseline until the progress is reset.
        $existingRanges = [[0, min($progress->watched_seconds, $progress->total_seconds)]];
      }

      $merged = \App\Services\LearningStreak\RangeMerger::merge(
        $existingRanges,
        $watchedRanges,
        $progress->total_seconds,
      );
      $progress->watched_ranges = $merged['ranges'];
      $progress->watched_seconds = $merged['total_seconds'];
    } elseif (($progress->watched_ranges ?? []) === []) {
      // Legacy clients do not send ranges. Keep their existing behavior until
      // all clients use range-based tracking.
      $progress->watched_seconds = max($progress->watched_seconds, $watchedSeconds);
    }

    if ($progress->total_seconds > 0 && $progress->watched_seconds >= ($progress->total_seconds * 0.9)) {
      $progress->is_completed = true;
    }

    $progress->save();

    // Recalculate course progress
    $courseProgressPercent = $this->updateCourseProgress($customerId, $courseId);

    return [
      'video_progress' => $progress,
      'course_progress_percent' => $courseProgressPercent,
    ];
  }

  /**
   * Submit quiz answers, evaluate, update progress, and recalculate course progress.
   */
  public function submitQuiz($customerId, $quizId, array $answersInput)
  {
    $quiz = \App\Models\Quiz::with(['questions.options'])->findOrFail($quizId);
    $courseId = $quiz->course_id;

    $totalQuestions = $quiz->questions->count();
    $correctCount = 0;
    $questionDetails = [];

    // Map answersInput by question_id for quick lookup
    $userAnswers = [];
    foreach ($answersInput as $ans) {
      if (is_array($ans) && isset($ans['question_id'])) {
        $qId = $ans['question_id'];
        $selIds = [];
        if (isset($ans['selected_option_ids']) && is_array($ans['selected_option_ids'])) {
          $selIds = array_map('intval', $ans['selected_option_ids']);
        } elseif (isset($ans['selected_option_id'])) {
          $selIds = [intval($ans['selected_option_id'])];
        }
        $userAnswers[$qId] = $selIds;
      }
    }

    foreach ($quiz->questions as $question) {
      $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->toArray();
      $selectedOptionIds = isset($userAnswers[$question->id]) ? $userAnswers[$question->id] : [];

      // Compare correct option IDs with selected option IDs
      sort($correctOptionIds);
      sort($selectedOptionIds);

      $isCorrect = false;
      if (count($correctOptionIds) > 0) {
        $isCorrect = ($correctOptionIds === $selectedOptionIds);
      }

      if ($isCorrect) {
        $correctCount++;
      }

      $questionDetails[] = [
        'question_id' => $question->id,
        'question_text' => $question->question_text,
        'is_correct' => $isCorrect,
        'correct_option_ids' => $correctOptionIds,
        'selected_option_ids' => $selectedOptionIds,
      ];
    }

    $isQuizCompleted = ($totalQuestions > 0 && $correctCount === $totalQuestions);

    // Mark quiz as completed
    $progress = \App\Models\CustomerQuizProgress::updateOrCreate(
      [
        'customer_id' => $customerId,
        'quiz_id' => $quizId,
      ],
      [
        'course_id' => $courseId,
        'is_completed' => $isQuizCompleted,
      ]
    );

    // Recalculate course progress
    $courseProgressPercent = $this->updateCourseProgress($customerId, $courseId);

    return [
      'quiz_id' => $quizId,
      'total_questions' => $totalQuestions,
      'correct_questions' => $correctCount,
      'score_percent' => $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0,
      'is_completed' => $isQuizCompleted,
      'course_progress_percent' => $courseProgressPercent,
      'details' => $questionDetails,
    ];
  }

  public function submitQuizLegacy($id, $data)
  {
    $quiz = \App\Models\Quiz::findOrFail($id);
    $customer = auth('customer')->user();

    $customerQuiz = \App\Models\CustomerQuiz::updateOrCreate(
      [
        'customer_id' => $customer->id,
        'quiz_id' => $id,
      ],
      [
        'is_passed' => $data['isPassed'],
      ]
    );

    return [
      'quiz_id' => $quiz->id,
      'is_passed' => $customerQuiz->is_passed,
      'is_completed' => $customerQuiz->is_passed,
    ];
  }
}
