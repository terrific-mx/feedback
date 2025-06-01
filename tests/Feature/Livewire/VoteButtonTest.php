<?php

use App\Models\Post;
use App\Models\User;
use Livewire\Volt\Volt;

it('guest cannot vote on a post', function () {
    $post = Post::factory()->create();

    Volt::test('vote-button', ['post' => $post])
        ->call('upvote')
        ->assertForbidden();

    $this->assertDatabaseCount('votes', 0);
    $this->assertEquals(0, $post->fresh()->votes()->count());
});

it('logged in user can vote on a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    Volt::actingAs($user)
        ->test('vote-button', ['post' => $post])
        ->call('upvote');

    $this->assertDatabaseCount('votes', 1);
    $this->assertDatabaseHas('votes', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
    $this->assertEquals(1, $post->fresh()->votes()->count());
});

it('logged in user can unvote a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    Volt::actingAs($user)
        ->test('vote-button', ['post' => $post])
        ->call('upvote');

    $this->assertDatabaseCount('votes', 1);

    Volt::actingAs($user)
        ->test('vote-button', ['post' => $post])
        ->call('upvote');

    $this->assertDatabaseCount('votes', 0);
    $this->assertEquals(0, $post->fresh()->votes()->count());
});

it('vote count is updated correctly', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $post = Post::factory()->create();

    Volt::actingAs($user1)
        ->test('vote-button', ['post' => $post])
        ->call('upvote');

    Volt::actingAs($user2)
        ->test('vote-button', ['post' => $post])
        ->call('upvote');

    $this->assertEquals(2, $post->fresh()->votes()->count());

    Volt::actingAs($user1)
        ->test('vote-button', ['post' => $post])
        ->call('upvote');

    $this->assertEquals(1, $post->fresh()->votes()->count());
});
