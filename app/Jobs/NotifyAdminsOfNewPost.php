<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Notifications\PostCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyAdminsOfNewPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $post,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adminEmails = config('feedback.admin_emails');

        foreach ($adminEmails as $adminEmail) {
            $admin = User::where('email', $adminEmail)->first();

            if ($admin) {
                $admin->notify((new PostCreated($this->post))->delay(now()->addMinutes(1)));
            }
        }
    }
}
