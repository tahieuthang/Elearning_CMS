<?php

namespace Tests\Unit;

use App\Services\VideoServices;
use Tests\TestCase;

class VideoMediaTest extends TestCase
{
    public function test_r2_path_is_rendered_as_a_direct_video_url(): void
    {
        $service = new VideoServices($this->createMock(\App\Services\S3Services::class));

        $html = $service->renderDirectVideo('https://pub.example.r2.dev/videos/2026/07/video.mp4');

        $this->assertStringContainsString('src="https://pub.example.r2.dev/videos/2026/07/video.mp4"', $html);
        $this->assertStringContainsString('<video', $html);
    }

    public function test_r2_object_path_is_converted_to_the_configured_public_url(): void
    {
        config(['filesystems.disks.r2.url' => 'https://pub.example.r2.dev/']);
        $service = new VideoServices($this->createMock(\App\Services\S3Services::class));

        $this->assertSame(
            'https://pub.example.r2.dev/videos/2026/07/video.mp4',
            $service->r2PublicUrl('videos/2026/07/video.mp4')
        );
    }
}
