<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public function __construct(public string $token) {}

  public function via($notifiable)
  {
    return ['mail'];
  }

  public function viaQueues(): array
  {
    return ['mail' => 'emails'];
  }

  public function toMail($notifiable)
  {
    $url = "http://localhost:5173/password-reset?token={$this->token}&email={$notifiable->email}";

    return (new MailMessage)
        ->subject('Reset Password Notification')
        ->greeting('Hello!')
        ->line('You are receiving this email because we received a password reset request for your account.')
        ->action('Reset Password', $url)
        ->line('If you did not request a password reset, no further action is required.');
  }
}
