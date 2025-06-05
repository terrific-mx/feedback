<?php

use App\Jobs\NotifyAdminsOfNewPost;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostCreated;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

it('admins are notified of new posts', function () {
    Notification::fake();

    // Create a user who will create the post
    $user = User::factory()->create();

    // Create admin users
    $admin1 = User::factory()->create(['email' => 'admin1@example.com']);
    $admin2 = User::factory()->create(['email' => 'admin2@example.com']);

    // Configure admin emails
    Config::set('feedback.admin_emails', [
        'admin1@example.com',
        'admin2@example.com',
        'nonexistent@example.com', // Should not receive notification
    ]);

    // Create a post
    $post = Post::factory()->create(['user_id' => $user->id]);

    // Dispatch the job
    NotifyAdminsOfNewPost::dispatch($post);

    // Assert that notifications were sent to the correct admins
    Notification::assertSentTo(
        [$admin1, $admin2],
        PostCreated::class,
        function (PostCreated $notification, array $channels) use ($post) {
            return $notification->post->id === $post->id && in_array('mail', $channels);
        }
    );

    // Assert that no notification was sent to the nonexistent admin
    $nonExistentAdmin = User::where('email', 'nonexistent@example.com')->first();
    Notification::assertNotSentTo(
        $nonExistentAdmin,
        PostCreated::class
    );
});
