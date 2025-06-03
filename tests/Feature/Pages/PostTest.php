<?php

use App\Models\Board;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    it('can create a post with images', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $image1 = UploadedFile::fake()->image('post_image1.jpg');
        $image2 = UploadedFile::fake()->image('post_image2.png');

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('title', 'Test title with images')
            ->set('description', 'Test description with images')
            ->set('images', [$image1, $image2])
            ->call('create');

        $post = Post::first();

        expect($post)->title->toBe('Test title with images')
            ->description->toBe('Test description with images')
            ->user_id->toBe($user->id)
            ->image_paths->toHaveCount(2);

        Storage::disk('public')->assertExists('post_images/' . $image1->hashName());
        Storage::disk('public')->assertExists('post_images/' . $image2->hashName());
    });

    it('validates pending images are of correct type and size', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(6000);

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('pendingImages', [$invalidFile, $largeImage])
            ->call('create')
            ->assertHasErrors(['pendingImages.0' => 'image', 'pendingImages.1' => 'max']);
    });

    it('limits pending image uploads to 4', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $images = [
            UploadedFile::fake()->image('img1.jpg'),
            UploadedFile::fake()->image('img2.jpg'),
            UploadedFile::fake()->image('img3.jpg'),
            UploadedFile::fake()->image('img4.jpg'),
            UploadedFile::fake()->image('img5.jpg'),
        ];

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('pendingImages', $images)
            ->call('create')
            ->assertHasErrors(['pendingImages' => 'max']);
    });

    it('limits image uploads to 4 in total initial plus pending', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $images = [
            UploadedFile::fake()->image('img1.jpg'),
            UploadedFile::fake()->image('img2.jpg'),
            UploadedFile::fake()->image('img3.jpg'),
        ];

        $component = Volt::actingAs($user)->test('pages.posts.create')
            ->set('title', 'Test title with images')
            ->set('description', 'Test description with images')
            ->set('pendingImages', $images)
            ->set('images', [UploadedFile::fake()->image('img4.jpg'), UploadedFile::fake()->image('img5.jpg')])
            ->call('create');

        expect(Post::first())->image_paths->toHaveCount(4);
    });

    it('validates images are of correct type and size', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(6000);

        Volt::actingAs($user)->test('pages.posts.create')
            ->set('images', [$invalidFile, $largeImage])
            ->assertHasErrors(['images.0' => 'image', 'images.1' => 'max']);
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
            ->set('board', $board->id)
            ->assertSee($post1->title)
            ->assertDontSee($post2->title);
    });

    it('shows all posts when switching back to "all"', function () {
        $board = Board::factory()->create();
        $post1 = Post::factory()->create(['title' => 'Post 1', 'board_id' => $board->id]);
        $post2 = Post::factory()->create(['title' => 'Post 2']);

        Volt::test('pages.index')
            ->set('board', $board->id)
            ->assertSee($post1->title)
            ->assertDontSee($post2->title)
            ->set('board', 'all')
            ->assertSee($post1->title)
            ->assertSee($post2->title);
    });
});

describe('sort', function () {
    it('defaults to sorting by top posts', function () {
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $post1->votes()->create(['user_id' => User::factory()->create()->id]);

        Volt::test('pages.index')
            ->assertSeeInOrder([$post1->title, $post2->title]);
    });

    it('sorts by newest posts', function () {
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();

        Volt::test('pages.index')
            ->set('sort', 'newest')
            ->assertSeeInOrder([$post2->title, $post1->title]);
    });

    it('sorts by top posts', function () {
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $post2->votes()->create(['user_id' => User::factory()->create()->id]);

        Volt::test('pages.index')
            ->set('sort', 'top')
            ->assertSeeInOrder([$post2->title, $post1->title]);
    });
});
