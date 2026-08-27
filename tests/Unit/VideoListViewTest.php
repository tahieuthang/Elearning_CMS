<?php

namespace Tests\Unit;

use Tests\TestCase;

class VideoListViewTest extends TestCase
{
    public function test_video_list_does_not_request_thumbnails_from_vimeo(): void
    {
        $viewSource = file_get_contents(resource_path('views/video/index.blade.php'));

        $this->assertStringNotContainsString('fetch_thumbnail_btn', $viewSource);
        $this->assertStringNotContainsString('/video/vimeo/thumbnail', $viewSource);
    }
}
