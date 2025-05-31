<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\VideoServices;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VideoController extends Controller
{
  private $videoServices;
  public function __construct(VideoServices $videoServices)
  {
    $this->videoServices = $videoServices;
  }

  public function getDetailVimeo($id, Request $request)
  {
    try {
      $vimeoUrl = $this->videoServices->getVimeo($id);
      return $this->successResponse(['vimeo' => $vimeoUrl]);
  } catch (NotFoundHttpException $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->notFoundErrorResponse();
  } catch (\Exception $e) {
      Helper::createLogError(__FILE__ . ':' .  __LINE__ . ' ' . $e);
      return $this->internalServerErrorResponse();
  }
  }
}
