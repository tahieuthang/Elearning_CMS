<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Vimeo\Laravel\Facades\Vimeo;
use App\Models\VideoUploading;
use Carbon\Carbon;
use Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UploadToVimeo implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $tries = 3;
  public $timeout = 300;

  protected $file;
  protected $options;
  protected $localfileId;
  protected $videoUploadingRecordId;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($file, $localfileId, $videoUploadingRecordId, array $options = [])
  {
    $this->file = $file;
    $this->localfileId = $localfileId;
    $this->videoUploadingRecordId = $videoUploadingRecordId;
    $this->options = $options;
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    try {
      // 1. Update job_id and status to in-progress
      if ($this->job) {
        VideoUploading::where('id', $this->videoUploadingRecordId)->update([
          'job_id' => (string)$this->job->getJobId(),
          'job_status' => config('constants.job_status.inProgress', 'inProgress'),
        ]);
      }

      // 2. Perform Vimeo API upload OUTSIDE DB transaction to avoid locking DB connections
      $vimeoVideoId = Vimeo::upload($this->file, $this->options);
      $vimeoCode = basename($vimeoVideoId);

      // 3. Update DB record with success status
      VideoUploading::where('id', $this->videoUploadingRecordId)
        ->update([
          'vimeo_id' => $vimeoCode,
          'file_path' => null,
          'updated_at' => Carbon::now()->toDateTimeString(),
          'job_status' => config('constants.job_status.success', 'success'),
          'error_log' => null,
        ]);

      // 4. Clean up local uploaded file
      $this->cleanUpFile();

    } catch (\Throwable $e) {
      Log::error("UploadToVimeo Job failed for record ID {$this->videoUploadingRecordId}: " . $e->getMessage(), [
        'file' => $this->file,
        'exception' => $e
      ]);

      VideoUploading::where('id', $this->videoUploadingRecordId)
        ->update([
          'vimeo_id' => null,
          'job_status' => config('constants.job_status.fail', 'fail'),
          'error_log' => $e->getMessage()
        ]);

      $this->cleanUpFile();
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
    Log::error("UploadToVimeo Job permanently failed for record ID {$this->videoUploadingRecordId}: " . $exception->getMessage());

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
