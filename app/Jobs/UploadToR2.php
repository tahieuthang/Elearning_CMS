<?php

namespace App\Jobs;

use App\Models\CourseVideo;
use App\Models\VideoUploading;
use App\Services\R2VideoThumbnailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * @param  string  $file  Local absolute file path
     * @param  string|int  $videoId
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
     */
    public function handle(R2VideoThumbnailService $thumbnailService): void
    {
        $uploadedObjectKeys = [];
        $thumbnailFile = null;

        try {
            if ($this->job) {
                VideoUploading::where('id', $this->videoUploadingRecordId)->update([
                    'job_id' => (string) $this->job->getJobId(),
                    'job_status' => config('constants.job_status.inProgress', 'inProgress'),
                ]);
            }

            if (! file_exists($this->file)) {
                throw new \Exception("Source file not found at path: {$this->file}");
            }

            $uploadId = (string) Str::uuid();
            $monthPath = now()->format('Y/m');
            $videoExtension = pathinfo($this->file, PATHINFO_EXTENSION) ?: 'mp4';
            $r2Path = "videos/{$monthPath}/{$uploadId}.{$videoExtension}";
            $thumbnailPath = "thumbnails/{$monthPath}/{$uploadId}.jpg";
            $thumbnailFile = dirname($this->file).DIRECTORY_SEPARATOR."{$uploadId}.jpg";

            $thumbnailService->generate($this->file, $thumbnailFile);

            $this->uploadFile($r2Path, $this->file, mime_content_type($this->file) ?: 'application/octet-stream');
            $uploadedObjectKeys[] = $r2Path;
            $this->uploadFile($thumbnailPath, $thumbnailFile, 'image/jpeg');
            $uploadedObjectKeys[] = $thumbnailPath;

            $r2PublicUrl = $this->r2PublicUrl($r2Path);
            $thumbnailPublicUrl = $this->r2PublicUrl($thumbnailPath);

            DB::transaction(function () use ($r2Path, $r2PublicUrl, $thumbnailPublicUrl) {
                VideoUploading::where('id', $this->videoUploadingRecordId)
                    ->update([
                        'vimeo_id' => $r2Path,
                        'file_path' => $r2PublicUrl,
                        'thumbnail_id' => $thumbnailPublicUrl,
                        'updated_at' => now(),
                        'job_status' => config('constants.job_status.success', 'success'),
                        'error_log' => null,
                    ]);

                CourseVideo::where('vimeo_id', $r2Path)->update([
                    'video_thumbnail' => $thumbnailPublicUrl,
                    'updated_at' => now(),
                ]);
            });

            $this->cleanUpFile();
        } catch (\Throwable $e) {
            $this->deleteUploadedObjects($uploadedObjectKeys);

            Log::error("UploadToR2 Job failed for record ID {$this->videoUploadingRecordId}: ".$e->getMessage(), [
                'file' => $this->file,
                'exception' => $e,
            ]);

            VideoUploading::where('id', $this->videoUploadingRecordId)
                ->update([
                    'job_status' => config('constants.job_status.fail', 'fail'),
                    'error_log' => $e->getMessage(),
                ]);

            throw $e;
        } finally {
            $this->cleanUpTemporaryFile($thumbnailFile);
        }
    }

    /**
     * Handle job failure after max retries.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("UploadToR2 Job permanently failed for record ID {$this->videoUploadingRecordId}: ".$exception->getMessage());

        VideoUploading::where('id', $this->videoUploadingRecordId)
            ->update([
                'job_status' => config('constants.job_status.fail', 'fail'),
                'error_log' => $exception->getMessage(),
            ]);

        $this->cleanUpFile();
    }

    /**
     * Clean up local temp file if it exists.
     */
    protected function cleanUpFile()
    {
        if (! empty($this->file) && file_exists($this->file)) {
            @unlink($this->file);
        }
    }

    private function uploadFile(string $path, string $sourceFile, string $contentType): void
    {
        $stream = fopen($sourceFile, 'rb');

        if (! is_resource($stream)) {
            throw new \RuntimeException("Unable to read source file at path: {$sourceFile}");
        }

        try {
            $uploaded = Storage::disk('r2')->put($path, $stream, [
                'ContentType' => $contentType,
            ]);
        } finally {
            fclose($stream);
        }

        if (! $uploaded) {
            throw new \RuntimeException("Failed to upload file to Cloudflare R2 at path: {$path}");
        }
    }

    private function r2PublicUrl(string $path): string
    {
        return rtrim((string) config('filesystems.disks.r2.url'), '/').'/'.ltrim($path, '/');
    }

    private function deleteUploadedObjects(array $objectKeys): void
    {
        foreach ($objectKeys as $objectKey) {
            try {
                Storage::disk('r2')->delete($objectKey);
            } catch (\Throwable $cleanupException) {
                Log::warning("Unable to remove incomplete R2 upload at path {$objectKey}: ".$cleanupException->getMessage());
            }
        }
    }

    private function cleanUpTemporaryFile(?string $file): void
    {
        if (! empty($file) && file_exists($file)) {
            @unlink($file);
        }
    }
}
