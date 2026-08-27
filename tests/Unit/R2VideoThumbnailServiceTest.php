<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Process;
use RuntimeException;
use Tests\TestCase;

class R2VideoThumbnailServiceTest extends TestCase
{
    private string $sourceFile;

    private string $thumbnailFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceFile = storage_path('app/test-r2-thumbnail-source.mp4');
        $this->thumbnailFile = storage_path('app/test-r2-thumbnail-output.jpg');

        file_put_contents($this->sourceFile, 'video-source');
    }

    protected function tearDown(): void
    {
        @unlink($this->sourceFile);
        @unlink($this->thumbnailFile);

        parent::tearDown();
    }

    public function test_it_generates_a_jpeg_thumbnail_with_ffmpeg(): void
    {
        $serviceClass = \App\Services\R2VideoThumbnailService::class;

        $this->assertTrue(class_exists($serviceClass));

        Process::fake(function () {
            file_put_contents($this->thumbnailFile, 'jpeg-thumbnail');

            return Process::result();
        });

        app($serviceClass)->generate($this->sourceFile, $this->thumbnailFile);

        $this->assertFileExists($this->thumbnailFile);
        Process::assertRan(function ($process) {
            return in_array('ffmpeg', $process->command, true)
                && in_array('-frames:v', $process->command, true)
                && in_array($this->sourceFile, $process->command, true)
                && in_array($this->thumbnailFile, $process->command, true);
        });
    }

    public function test_it_throws_when_ffmpeg_cannot_generate_a_thumbnail(): void
    {
        $serviceClass = \App\Services\R2VideoThumbnailService::class;

        $this->assertTrue(class_exists($serviceClass));

        Process::fake([
            '*' => Process::result(errorOutput: 'invalid video stream', exitCode: 1),
        ]);

        $this->expectException(RuntimeException::class);

        app($serviceClass)->generate($this->sourceFile, $this->thumbnailFile);
    }
}
