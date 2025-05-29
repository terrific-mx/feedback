<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

describe('index', function () {
    it('loads successfully', function () {
        get('/')
            ->assertOk();
    });

    it('shows posts when they exist', function () {
        $post = Post::factory()->create();

        get('/')
            ->assertSee($post->title)
            ->assertSee($post->description);
    });
});

describe('create', function () {
    it('redirects unauthenticated users', function () {
        get('/posts/create')
            ->assertRedirect('/login');
    });

    it('can create a post', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('title', 'Test title')
            ->set('description', 'Test description')
            ->call('create');

        expect(Post::first())->title->toBe('Test title')
            ->description->toBe('Test description')
            ->user_id->toBe($user->id);
    });

    it('requires title', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('description', 'Test description')
            ->call('create')
            ->assertHasErrors(['title' => 'required']);
    });

    it('requires description', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('title', 'Test title')
            ->call('create')
            ->assertHasErrors(['description' => 'required']);
    });

    it('validates title max length', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('title', str_repeat('a', 256))
            ->set('description', 'Test description')
            ->call('create')
            ->assertHasErrors(['title' => 'max']);
    });
});
