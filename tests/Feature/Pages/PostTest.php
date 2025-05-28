<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

describe('create', function () {
    it('redirects unauthenticated users', function () {
        get('/posts/create')
            ->assertRedirect('/login');
    });

    it('can create a post', function () {
        $user = User::factory()->create();

        $component = Volt::actingAs($user)->test('pages.posts.create')
            ->set('title', 'Test title')
            ->set('description', 'Test description')
            ->call('create');

        expect(Post::first())->title->toBe('Test title')
            ->description->toBe('Test description')
            ->user_id->toBe($user->id);
    });
});
