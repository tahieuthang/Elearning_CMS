<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use RuntimeException;

class R2VideoThumbnailService
{
    public function generate(string $sourceFile, string $thumbnailFile): void
    {
        $result = Process::timeout(60)->run([
            'ffmpeg',
            '-y',
            '-ss',
            '00:00:01',
            '-i',
            $sourceFile,
            '-frames:v',
            '1',
            '-q:v',
            '2',
            $thumbnailFile,
        ]);

        if ($result->failed() || ! is_file($thumbnailFile) || filesize($thumbnailFile) === 0) {
            throw new RuntimeException('Unable to generate a JPEG thumbnail from the uploaded video.');
        }
    }
}
