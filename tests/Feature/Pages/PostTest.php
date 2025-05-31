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

    it('can change the board of a post', function () {
        $post = Post::factory()->create();
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);
        $board = Board::factory()->create();

        Volt::actingAs($adminUser)->test('pages.posts.show', ['post' => $post])
            ->set('board', $board->id)
            ->call('changeBoard');

        expect($post->fresh())->board_id->toBe($board->id);
    });

    it('requires authentication to change the board', function () {
        $post = Post::factory()->create();
        $board = Board::factory()->create();

        Volt::test('pages.posts.show', ['post' => $post])
            ->set('board', $board->id)
            ->call('changeBoard')
            ->assertForbidden();
    });

    it('validates the board exists', function () {
        $post = Post::factory()->create();
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);

        Volt::actingAs($adminUser)->test('pages.posts.show', ['post' => $post])
            ->set('board', 999)
            ->call('changeBoard')
            ->assertHasErrors(['board' => 'exists']);
    });
});

describe('board filter', function () {
    it('defaults to showing all posts', function () {
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        Volt::test('pages.index')
            ->assertSee($post1->title)
            ->assertSee($post2->title);
    });

    it('filters posts by board', function () {
        $board = Board::factory()->create();
        $post1 = Post::factory()->create(['title' => 'Post 1', 'board_id' => $board->id]);
        $post2 = Post::factory()->create(['title' => 'Post 2']);

        Volt::test('pages.index')
            ->set('board_filter', $board->id)
            ->assertSee($post1->title)
            ->assertDontSee($post2->title);
    });

    it('shows all posts when switching back to "all"', function () {
        $board = Board::factory()->create();
        $post1 = Post::factory()->create(['title' => 'Post 1', 'board_id' => $board->id]);
        $post2 = Post::factory()->create(['title' => 'Post 2']);

        Volt::test('pages.index')
            ->set('board_filter', $board->id)
            ->assertSee($post1->title)
            ->assertDontSee($post2->title)
            ->set('board_filter', 'all')
            ->assertSee($post1->title)
            ->assertSee($post2->title);
    });
});
