<?php

use App\Models\Board;
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

    it('creates a post with associated board', function () {
        $user = User::factory()->create();
        $board = Board::factory()->create();

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('title', 'Test title')
            ->set('description', 'Test description')
            ->set('board', $board->id)
            ->call('create');

        expect(Post::first())->title->toBe('Test title')
            ->description->toBe('Test description')
            ->user_id->toBe($user->id)
            ->board_id->toBe($board->id);
    });

    it('validates board exists', function () {
        $user = User::factory()->create();

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('board', 999)
            ->call('create')
            ->assertHasErrors(['board' => 'exists']);
    });
});

describe('show', function () {
    it('can create comments', function () {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        Volt::actingAs($user)->test('pages.posts.show', ['post' => $post])
            ->set('description', 'Test comment')
            ->call('comment');

        expect($post->fresh()->comments->first())
            ->description->toBe('Test comment')
            ->user_id->toBe($user->id);
    });

    it('requires authentication to create comments', function () {
        $post = Post::factory()->create();

        Volt::test('pages.posts.show', ['post' => $post])
            ->set('description', 'Test comment')
            ->call('comment')
            ->assertForbidden();
    });

    it('can change the status of a post', function () {
        $post = Post::factory()->pending()->create();
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);

        Volt::actingAs($adminUser)->test('pages.posts.show', ['post' => $post])
            ->set('status', 'completed')
            ->call('changeStatus');

        expect($post->fresh())->status->toBe('completed');
    });

    it('requires authentication to change the status', function () {
        $post = Post::factory()->create(['status' => 'pending']);

        Volt::test('pages.posts.show', ['post' => $post])
            ->set('status', 'completed')
            ->call('changeStatus')
            ->assertForbidden();
    });

    it('validates the status is either pending or completed', function () {
        $post = Post::factory()->create(['status' => 'pending']);
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);

        Volt::actingAs($adminUser)->test('pages.posts.show', ['post' => $post])
            ->set('status', 'invalid-status')
            ->call('changeStatus')
            ->assertHasErrors(['status' => 'in']);
    });
});
