<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Order;
use App\Services\CourseServices;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
  protected $courseServices;
  public function __construct(CourseServices $courseServices)
  {
    $this->courseServices = $courseServices;
  }
  public function getCourseList(Request $request)
  {
    try {
      if ($request->has('my_courses')) {
        $request->merge([
          'my_courses' => filter_var($request->my_courses, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
        ]);
      }
      $request->validate([
        "category_name" => ["nullable", 'array'],
        "tag_name" => ["nullable", 'array'],
        "status" => ["nullable", 'array'],
        "keyword" => ["nullable"],
        "page" => "nullable|numeric|min:1",
        "per_page" => "nullable|numeric|min:1",
        "my_courses" => "nullable|boolean",
        "rating_average" => "nullable|numeric|min:0|max:5",
        "sort_by" => "nullable|string|in:price-low,price-high,rating,relevant",
      ]);
      $data = $this->courseServices->getCoursesList($request->all());
      return $this->successResponse([
        'data' => $this->courseServices->formatCourseData($data->items()),
        'total' => $data->total(),
        'count' => $data->count(),
        'per_page' => $data->perPage(),
        'current_page' => $data->currentPage(),
        'total_pages' => $data->lastPage()
      ]);
    } catch (ValidationException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->badRequestErrorResponse();
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getCourseDetail($id)
  {
    try {
      $data = $this->courseServices->getCourseById($id);
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getCourseTop()
  {
    try {
      $data = $this->courseServices->getCourseTop();
      return $this->successResponse([
        'data' => $data['courseList']
      ]);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getReviewByCourse($id, Request $request) {
    try {
      $data = $this->courseServices->getReviewByCourse($id, $request);
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function addReviews($id, Request $request)
  {
    DB::beginTransaction();
    try {
      $request->validate([
        "comment" => 'required',
        "rate" => 'required'
      ]);
      $this->courseServices->addReviews([
        'course_id' => $id,
        'comment' => $request->comment,
        'rate' => $request->rate
      ]);
      DB::commit();
      return $this->createdSuccessResponse();
    } catch (ValidationException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      DB::rollBack();
      return response()->json([
        'status' => 422,
        'error' => 'Validation failed.',
        'errors' => $e->errors(),
      ], 422); 
    } catch (\Exception $e) {
      DB::rollBack();
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e->getMessage());
      return $this->internalServerErrorResponse();
    } 
  }

  public function getCategoryBestOfUser() {
    try {
      $data = $this->courseServices->getCategoryBestOfUser();
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getNewCourses($id) {
    try {
      $data = $this->courseServices->getNewCourses($id);
      return $this->successResponse($data);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getCategories() {
    try {
      $categories = \App\Models\PostCategory::select('id', 'category_name')->get();
      return $this->successResponse($categories);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function getTags() {
    try {
      $tags = \App\Models\Tag::select('id', 'tag_name')->get();
      return $this->successResponse($tags);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function postVideoProgress(Request $request)
  {
    try {
      $request->validate([
        'course_video_id' => 'required|exists:course_videos,id',
        'watched_seconds' => 'required|integer|min:0',
        'total_seconds' => 'required|integer|min:1',
        'is_completed' => 'nullable'
      ]);

      $customerId = auth('customer')->user()->id;

      $result = $this->courseServices->trackVideoProgress(
        $customerId,
        $request->course_video_id,
        $request->watched_seconds,
        $request->total_seconds,
        $request->is_completed
      );

      return $this->successResponse($result);
    } catch (ValidationException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return response()->json([
        'status' => 422,
        'message' => 'Validation failed.',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }

  public function submitQuiz(Request $request)
  {
    try {
      $request->validate([
        'quiz_id' => 'required|exists:quizzes,id',
        'answers' => 'required|array',
        'answers.*.question_id' => 'required|integer',
        'answers.*.selected_option_id' => 'nullable|integer',
        'answers.*.selected_option_ids' => 'nullable|array',
        'answers.*.selected_option_ids.*' => 'integer',
      ]);

      $customerId = auth('customer')->user()->id;

      $result = $this->courseServices->submitQuiz(
        $customerId,
        $request->quiz_id,
        $request->answers
      );

      return $this->successResponse($result);
    } catch (ValidationException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return response()->json([
        'status' => 422,
        'message' => 'Validation failed.',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
    }
  }
}
