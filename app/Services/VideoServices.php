<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Tag;
use App\Models\Post;
use App\Helpers\Helper;
use App\Services\S3Services;
use App\Models\VideoUploading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\UploadToVimeo;
use App\Jobs\UploadToR2;
use Illuminate\Support\Facades\Storage;
use Vimeo\Laravel\Facades\Vimeo;
use Illuminate\Http\Request;
use Laravel\Ui\Presets\React;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Models\CourseVideo;

class VideoServices
{
  protected $S3Services;
  public function __construct(S3Services $S3Services)
  {
    $this->S3Services = $S3Services;
  }
  public function getVideos()
  {
    $videos = VideoUploading::where('job_status', config('constants.job_status.success'))
      ->orderBy('job_id', 'desc')->get();
    return $videos;
  }

  public function getVimeo($id)
  {
    $courseVideo = CourseVideo::find($id);
    if (!$courseVideo) {
      throw new NotFoundHttpException(__('Not found Video!'));
    }

    $vimeoId = (string) $courseVideo->vimeo_id;

    // 1. Check if vimeo_id itself is a direct R2 Public URL
    if ($this->isHttpUrl($vimeoId)) {
      return $this->renderDirectVideo($vimeoId);
    }

    if ($this->isR2ObjectKey($vimeoId)) {
      return $this->renderDirectVideo($this->r2PublicUrl($vimeoId));
    }

    // 2. Check in video_uploadings table by vimeo_id or video_id or id
    $uploading = VideoUploading::where('vimeo_id', $vimeoId)
      ->orWhere('file_path', $vimeoId)
      ->orWhere('video_id', $vimeoId)
      ->orWhere('id', $vimeoId)
      ->first();

    if ($uploading && !empty($uploading->file_path)) {
      if ($this->isHttpUrl($uploading->file_path)) {
        return $this->renderDirectVideo($uploading->file_path);
      }
    }

    if ($uploading && !empty($uploading->vimeo_id) && $this->isR2ObjectKey($uploading->vimeo_id)) {
      return $this->renderDirectVideo($this->r2PublicUrl($uploading->vimeo_id));
    }

    // 3. Fallback: Vimeo embed iframe
    try {
      $vimeoApi = '/videos/' . $vimeoId;
      $vimeoUrl = Vimeo::request($vimeoApi, [], 'GET');

      if (!empty($vimeoUrl['body']['embed']['html'])) {
        return $vimeoUrl['body']['embed']['html'];
      }
    } catch (\Throwable $e) {
      Log::warning("Vimeo fetch failed for ID {$vimeoId}: " . $e->getMessage());
    }

    return '';
  }

  public function renderDirectVideo(string $url): string
  {
    return '<video controls autoplay controlsList="nodownload" style="width:100%;height:100%;object-fit:contain;" src="' . e($url) . '"></video>';
  }

  public function r2PublicUrl(string $path): string
  {
    if ($this->isHttpUrl($path)) {
      return $path;
    }

    return rtrim((string) config('filesystems.disks.r2.url'), '/') . '/' . ltrim($path, '/');
  }

  private function isHttpUrl(?string $value): bool
  {
    return is_string($value) && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'));
  }

  private function isR2ObjectKey(?string $value): bool
  {
    return is_string($value) && str_starts_with(ltrim($value, '/'), 'videos/');
  }

  public function formatVideoData($data)
  {
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('video_id', function ($row) {
        return $row->video_id;
      })
      ->addColumn('created_at', function ($row) {
        return $row->created_at;
      })
      ->addColumn('error_log', function ($row) {
        return $row->error_log;
      })
      ->addColumn('job_status', function ($row) {
        switch ($row->job_status) {
          case config('constants.job_status.inProgress'):
            return '<div class="badge bg-warning">Đang tải lên</div>';
          case config('constants.job_status.success'):
            return '<div class="badge bg-success">Thành công</div>';
          case config('constants.job_status.fail'):
            return '<div class="badge bg-danger">Thất bại</div>';
          default:
            return '';
        }
      })
      ->addColumn('action', function ($row) {
        if ($row->job_status === config('constants.job_status.fail')) {
          return '<button type="button" class="btn btn-block btn-warning">Cập nhật</button>';
        }
        return '';
      })
      ->rawColumns(['job_status', 'action'])
      ->make(true);
  }

  public function formatVideoDatatables($filterData)
  {
    return Datatables::of($filterData)
      ->addIndexColumn()
      ->addColumn('title', function ($row) {
        return $row->video_id;
      })
      ->addColumn('videoThumbnail', function ($row) {
        if (!empty($row->thumbnail_id)) {
          return '<img style="width: 120px;height: 120px;" src="' . $row->thumbnail_id . '" />';
        }
        return '<img style="width: 120px;height: 120px;" src="' . url('/images/default_image.jpg') . '" />';
      })
      ->addColumn('action', function ($row) {
        return '<button type="button" class="btn btn-block btn-info btn-info-video" style="width: 130px;" video-id="' . e($row->id) . '">Xem video</button>';
      })
      ->rawColumns(['action', 'videoThumbnail'])
      ->make(true);
  }

  public function getProcessUploadVideoList()
  {
    $videoList = VideoUploading::all();
    return $videoList;
  }
  // tạo dữ liệu preview cho từng file được upload, giúp cho các chức năng khác trong ứng dụng dễ dàng sử dụng
  public function processUploadChunkVideo(Request $data)
  {
    $preview = $config = $errors = [];
    $targetDir = public_path('uploads');
    if (!file_exists($targetDir)) {
      @mkdir($targetDir);
    }

    $fileBlob = 'fileBlob';
    if (isset($_FILES[$fileBlob])) {
      $file = $_FILES[$fileBlob]['tmp_name'];
      $fileName = $_POST['fileName'];
      $fileSize = $_POST['fileSize'];
      $fileId = $_POST['fileId'];
      $index =  $_POST['chunkIndex'];
      $totalChunks = $_POST['chunkCount'];     // the total number of chunks for this file
      $targetFile = $targetDir . '/' . $fileName;  // your target file path
      if ($totalChunks > 1) {                  // create chunk files only if chunks are greater than 1
        $targetFile .= '_' . str_pad($index, 4, '0', STR_PAD_LEFT);
      }

      $uploadResult = move_uploaded_file($file, $targetFile);
      if ($uploadResult) {
        $isCompleted = false;
        if ($totalChunks > 1) {
          $chunks = glob("{$targetDir}/{$fileName}_*");
          if (count($chunks) == $totalChunks) {
            $outFile = $targetDir . '/' . $fileName;
            $this->combineChunks($chunks, $outFile);
            $isCompleted = true;
          }
        } else {
          $isCompleted = true;
        }

        // Trigger saveVideoId and dispatch Job when file is completely assembled on server
        if ($isCompleted) {
          $durationSeconds = isset($_POST['duration_seconds']) && is_numeric($_POST['duration_seconds'])
            ? (int) $_POST['duration_seconds']
            : null;
          $this->saveVideoId($fileId, 'uploads/' . $fileName, $durationSeconds);
        }

        $zoomUrl = '/uploads/' . $fileName;

        return [
          'chunkIndex' => $index,         // the chunk index processed
          'initialPreview' => '', // the thumbnail preview data (e.g. image)
          'initialPreviewConfig' => [
            [
              'type' => 'image',      // check previewTypes (set it to 'other' if you want no content preview)
              'caption' => $fileName, // caption
              'key' => $fileId,       // keys for deleting/reorganizing preview
              'fileId' => $fileId,    // file identifier
              'size' => $fileSize,    // file size
              'zoomData' => $zoomUrl, // separate larger zoom data
            ]
          ],
          'append' => true
        ];
      } else {
        return [
          'error' => 'Error uploading chunk ' . $_POST['chunkIndex']
        ];
      }
    }
    return [
      'error' => 'No file found'
    ];
  }


  function combineChunks($chunks, $targetFile)
  {
    // open target file handle
    $handle = fopen($targetFile, 'a+');

    foreach ($chunks as $file) {
      fwrite($handle, file_get_contents($file));
    }
    foreach ($chunks as $file) {
      @unlink($file);
    }
    // close the file handle
    fclose($handle);
  }

  public function saveVideoId($videoId, $filePath, $durationSeconds = null)
  {
    try {
      $cleanPath = ltrim($filePath, '/');
      $fullPath = public_path($cleanPath);

      // Fallback check: If target file does not exist at fullPath, check if it exists under public/uploads/
      if (!file_exists($fullPath)) {
        $baseName = basename($filePath);
        $fallbackPath = public_path('uploads/' . $baseName);
        if (file_exists($fallbackPath)) {
          $filePath = 'uploads/' . $baseName;
          $fullPath = $fallbackPath;
        }
      }

      $data = [
        'video_id' => $videoId,
        'file_path' => $filePath,
        'duration_seconds' => $durationSeconds && $durationSeconds > 0 ? $durationSeconds : null,
        'created_at' => Carbon::now()->toDateTimeString()
      ];
      $videoUploadingRecordId = VideoUploading::insertGetId($data);
      $driver = env('VIDEO_STORAGE_DRIVER', 'r2');

      if ($driver === 'vimeo') {
        $video_name = \explode('/', $filePath)[2] ?? 'default_video_name';
        $options = [
          'name' => $video_name,
          'description' => 'test video'
        ];
        UploadToVimeo::dispatch($fullPath, $videoId, $videoUploadingRecordId, $options);
      } else {
        UploadToR2::dispatch($fullPath, $videoId, $videoUploadingRecordId);
      }

      return [
        'status' => true,
      ];
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function processDeleteVideo($id)
  {
    try {
      $video = VideoUploading::where('id', $id)->first();
      if ($video) {
        if (!empty($video->vimeo_id)) {
          // If vimeo_id starts with 'videos/' it is stored on Cloudflare R2
          if (str_starts_with($video->vimeo_id, 'videos/')) {
            Storage::disk('r2')->delete($video->vimeo_id);
          } else {
            Vimeo::request("/videos/{$video->vimeo_id}", [], 'DELETE');
          }
        }
        $video->delete();
      }
      return [
        'status' => true
      ];
    } catch (\Exception $e) {
      return [
        'status' => false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function updateThumbnail()
  {
    $result = [
      'updated' => 0,
      'fallback' => 0,
      'skipped' => 0,
      'errors' => [],
    ];

    $videoNullThumbnailData = VideoUploading::where(function ($query) {
      $query->whereNull('thumbnail_id')
        ->orWhere('thumbnail_id', '');
    })->get();

    foreach ($videoNullThumbnailData as $videoNullThumbnail) {
      $vimeoId = $videoNullThumbnail->vimeo_id ?? '';
      $filePath = $videoNullThumbnail->file_path ?? '';

      // Check if video is stored on R2
      if (
        str_starts_with($vimeoId, 'http://') ||
        str_starts_with($vimeoId, 'https://') ||
        str_starts_with($vimeoId, 'videos/') ||
        str_starts_with($filePath, 'http://') ||
        str_starts_with($filePath, 'https://')
      ) {
        $videoNullThumbnail->update(['thumbnail_id' => url('/images/default_image.jpg')]);
        $result['updated']++;
        $result['fallback']++;
        continue;
      }

      // For Vimeo videos
      try {
        if (!empty($vimeoId)) {
          $thumbnailApiLink = "/videos/" . $vimeoId . "/pictures";
          $vimeoThumbnail = Vimeo::request($thumbnailApiLink, ['per_page' => 1], 'GET');

          if (!empty($vimeoThumbnail['body']['data']) && count($vimeoThumbnail['body']['data']) > 0) {
            $thumbnailUrl = empty($vimeoThumbnail['body']['data'][0]['base_link'])
              ? $vimeoThumbnail['body']['data'][0]['link']
              : $vimeoThumbnail['body']['data'][0]['base_link'];

            $videoNullThumbnail->update([
              'thumbnail_id' => $thumbnailUrl
            ]);
            $result['updated']++;
          } else {
            $result['skipped']++;
          }
        }
      } catch (\Throwable $e) {
        Log::warning("Vimeo thumbnail update failed for {$vimeoId}: " . $e->getMessage());
        $result['errors'][] = [
          'id' => $videoNullThumbnail->id,
          'message' => $e->getMessage(),
        ];
      }
    }

    return $result;
  }

  public function formatVideoListDataTableForCreateCource($data)
  {
    return Datatables::of($data)
      ->addColumn('check', function ($row) {
        return '<input type="checkbox" class="form-checkbox-input">';
      })
      ->addIndexColumn()
      ->addColumn('title', function ($row) {
        return $row->video_id;
      })
      ->addColumn('videoThumbnail', function ($row) {
        if (!empty($row->thumbnail_id)) {
          return '<img style="width: 120px;height: 120px;" src="' . $row->thumbnail_id . '" />';
        }
        return '<img style="width: 120px;height: 120px;" src="' . url('/images/default_image.png') . '" />';
      })
      ->addColumn('created_at', function ($row) {
        return $row->created_at;
      })
      ->addColumn('action', function ($row) {
        return '<button type="button" class="btn btn-block btn-info btn-info-video" style=" width: 130px; " video-id="' . e($row->id) . '">Xem video</button>';
      })
      ->rawColumns(['videoThumbnail', 'check', 'action'])
      ->make(true);
  }
}
