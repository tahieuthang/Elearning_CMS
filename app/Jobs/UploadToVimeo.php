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

  protected $file;
  protected $options;
  protected $localfileId;
  protected $videoUploadingRecordId;
  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct($file, $localfileId, $videoUploadingRecordId, $options = [])
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
    DB::beginTransaction();
    try {
      VideoUploading::where('id', $this->videoUploadingRecordId)->update(['job_id' => $this->job->getJobId()]);

      // Ensure the file exists before attempting upload
      if (!file_exists($this->file)) {
        Log::error('UploadToVimeo: file not found before upload', ['file' => $this->file, 'video_uploading_id' => $this->videoUploadingRecordId]);
        VideoUploading::where('id', $this->videoUploadingRecordId)
          ->update([
            'vimeo_id' => null,
            'job_status' => config('constants.job_status.fail'),
            'error_log' => 'file not found: ' . $this->file
          ]);
        DB::commit();
        return;
      }

      $vimeoVideoId = Vimeo::upload($this->file, $this->options);
      Log::info('UploadToVimeo: uploaded to Vimeo', ['vimeoVideoId' => $vimeoVideoId, 'file' => $this->file]);
      $vimeoCode = basename($vimeoVideoId);
      VideoUploading::where('id', $this->videoUploadingRecordId)
        ->update([
          'vimeo_id' => $vimeoCode,
          'file_path' => null,
          'updated_at' => Carbon::now()->toDateTimeString(),
          'job_status' => config('constants.job_status.success')
        ]);
      DB::commit();
      // unlink($this->file);
    } catch (\Exception $e) {
      Log::error('UploadToVimeo failed', ['exception' => (string) $e, 'file' => $this->file, 'video_uploading_id' => $this->videoUploadingRecordId]);
      DB::rollBack();
      DB::beginTransaction();
      VideoUploading::where('id', $this->videoUploadingRecordId)
        ->update([
          'vimeo_id' => null,
          'job_status' => config('constants.job_status.fail'),
          'error_log' => $e->getMessage()
        ]);
      DB::commit();
    }
  }
}
