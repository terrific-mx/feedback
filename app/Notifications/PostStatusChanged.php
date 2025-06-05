<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Post $post,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $postUrl = url("/posts/{$this->post->id}");

        return (new MailMessage)
            ->subject(__('Post Status Changed: :title', ['title' => $this->post->title]))
            ->line(__('The status of ":title" has been changed.', ['title' => $this->post->title]))
            ->line(__('From: :oldStatus', ['oldStatus' => $this->oldStatus]))
            ->line(__('To: :newStatus', ['newStatus' => $this->newStatus]))
            ->action(__('View Post'), $postUrl)
            ->line(__('Thank you for using our application!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
