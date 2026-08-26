<?php

namespace Tests\Unit;

use App\Events\OrderPaymentCompletedBroadcast;
use App\Jobs\UploadToR2;
use App\Jobs\UploadToVimeo;
use App\Notifications\CustomerActiveNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPUnit\Framework\TestCase;

class QueueRoutingTest extends TestCase
{
    public function test_video_upload_jobs_are_routed_to_the_uploads_queue(): void
    {
        $r2Job = new UploadToR2('/tmp/video.mp4', 1, 1);
        $vimeoJob = new UploadToVimeo('/tmp/video.mp4', 1, 1);

        $this->assertSame('uploads', $r2Job->queue);
        $this->assertSame('uploads', $vimeoJob->queue);
    }

    public function test_mail_notifications_are_queued_on_the_emails_queue(): void
    {
        $activationNotification = new CustomerActiveNotification('123456');
        $passwordResetNotification = new ResetPasswordNotification('token');

        $this->assertInstanceOf(ShouldQueue::class, $activationNotification);
        $this->assertInstanceOf(ShouldQueue::class, $passwordResetNotification);
        $this->assertSame(['mail' => 'emails'], $activationNotification->viaQueues());
        $this->assertSame(['mail' => 'emails'], $passwordResetNotification->viaQueues());
    }

    public function test_order_payment_broadcast_is_routed_to_the_notifications_queue(): void
    {
        $event = new OrderPaymentCompletedBroadcast(1, []);

        $this->assertSame('notifications', $event->broadcastQueue());
    }
}
