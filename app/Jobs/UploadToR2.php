<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\VideoUploading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UploadToR2 implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $tries = 3;
  public $timeout = 600;

  protected $file;
  protected $videoId;
  protected $videoUploadingRecordId;

  /**
   * Create a new job instance.
   *
   * @param string $file Local absolute file path
   * @param string|int $videoId
   * @param int $videoUploadingRecordId
   */
  public function __construct(string $file, $videoId, int $videoUploadingRecordId)
  {
    $this->onQueue('uploads');
    $this->file = $file;
    $this->videoId = $videoId;
    $this->videoUploadingRecordId = $videoUploadingRecordId;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      // 1. Update job_id and status to inProgress
      if ($this->job) {
        VideoUploading::where('id', $this->videoUploadingRecordId)->update([
          'job_id' => (string)$this->job->getJobId(),
          'job_status' => config('constants.job_status.inProgress', 'inProgress'),
        ]);
      }

      if (!file_exists($this->file)) {
        throw new \Exception("Source file not found at path: {$this->file}");
      }

      $fileName = basename($this->file);
      $r2Path = 'videos/' . date('Y/m') . '/' . time() . '_' . $fileName;

      // 2. Stream upload to Cloudflare R2 disk
      $stream = fopen($this->file, 'r+');
      $uploaded = Storage::disk('r2')->put($r2Path, $stream);
      if (is_resource($stream)) {
        fclose($stream);
      }

      if (!$uploaded) {
        throw new \Exception("Failed to upload file to Cloudflare R2 disk.");
      }

      $r2PublicUrl = rtrim(config('filesystems.disks.r2.url', ''), '/') . '/' . ltrim($r2Path, '/');

      // 3. Update DB record with R2 public URL and success status
      VideoUploading::where('id', $this->videoUploadingRecordId)
        ->update([
          'vimeo_id' => $r2Path,
          'file_path' => $r2PublicUrl,
          'updated_at' => Carbon::now()->toDateTimeString(),
          'job_status' => config('constants.job_status.success', 'success'),
          'error_log' => null,
        ]);

      // 4. Clean up local uploaded file
      $this->cleanUpFile();

    } catch (\Throwable $e) {
      Log::error("UploadToR2 Job failed for record ID {$this->videoUploadingRecordId}: " . $e->getMessage(), [
        'file' => $this->file,
        'exception' => $e
      ]);

      VideoUploading::where('id', $this->videoUploadingRecordId)
        ->update([
          'job_status' => config('constants.job_status.fail', 'fail'),
          'error_log' => $e->getMessage()
        ]);

      throw $e;
    }
  }

  /**
   * Handle job failure after max retries.
   *
   * @param \Throwable $exception
   * @return void
   */
  public function failed(\Throwable $exception)
  {
    Log::error("UploadToR2 Job permanently failed for record ID {$this->videoUploadingRecordId}: " . $exception->getMessage());

    VideoUploading::where('id', $this->videoUploadingRecordId)
      ->update([
        'job_status' => config('constants.job_status.fail', 'fail'),
        'error_log' => $exception->getMessage()
      ]);

    $this->cleanUpFile();
  }

  /**
   * Clean up local temp file if it exists.
   */
  protected function cleanUpFile()
  {
    if (!empty($this->file) && file_exists($this->file)) {
      @unlink($this->file);
    }
  }
}
