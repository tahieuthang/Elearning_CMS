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
      $request->validate([
        "category_name" => ["nullable", 'array'],
        "tag_name" => ["nullable", 'array'],
        "status" => ["nullable", 'array'],
        "keyword" => ["nullable"],
        "page" => "nullable|numeric|min:1",
        "per_page" => "nullable|numeric|min:1",
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
    // Log incoming request for debugging
    Log::info('API addReviews called', ['course_id' => $id, 'payload' => $request->all()]);

    if (!auth('customer')->check()) {
      Log::error('addReviews unauthorized attempt', ['course_id' => $id, 'payload' => $request->all()]);
      return response()->json([
        'status' => 401,
        'message' => 'Unauthorized'
      ], 401);
    }

    try {
      // Start DB transaction inside try so DB connection errors are caught
      DB::beginTransaction();

      $request->validate([
        "comment" => 'required',
        // ensure numeric and in sensible range
        "rate" => 'required|numeric|min:1|max:5'
      ]);

      Log::info('addReviews validated payload', ['comment' => $request->comment, 'rate' => $request->rate, 'course_id' => $id, 'user_id' => auth('customer')->id()]);

      $review = $this->courseServices->addReviews([
        'course_id' => $id,
        'comment' => $request->comment,
        'rate' => $request->rate
      ]);

      Log::info('addReviews service returned', ['result' => $review ? 'created' : 'false']);

      if (!$review) {
        DB::rollBack();
        return response()->json([
          'status' => 403,
          'message' => 'Bạn phải mua khóa học mới được đánh giá'
        ], 403);
      }

      DB::commit();
      return $this->createdSuccessResponse();
    } catch (ValidationException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
  // If transaction started, rollback
  try { DB::rollBack(); } catch (\Exception $_) {}
      return response()->json([
        'status' => 422,
        'error' => 'Validation failed.',
        'errors' => $e->errors(),
      ], 422);
  } catch (\Throwable $e) {
      // Catch any DB connection / runtime errors and return a clearer JSON response
      // Log full trace + payload to help debugging
      Log::error('addReviews - unexpected error', [
        'exception' => $e->getMessage(),
        'trace' => method_exists($e, 'getTraceAsString') ? $e->getTraceAsString() : null,
        'payload' => $request->all(),
        'course_id' => $id,
        'user_id' => auth('customer')->id()
      ]);

  try { DB::rollBack(); } catch (\Exception $_) {}

      $message = 'Internal server error';
      // If app debug is enabled, return exception message to help debugging on demo
      if (config('app.debug')) {
        $message = $e->getMessage();
      }

      return response()->json([
        'status' => 500,
        'error' => $message
      ], 500);
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
}
