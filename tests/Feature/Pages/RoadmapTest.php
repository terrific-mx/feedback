<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('loads the roadmap page', function () {
    get('/roadmap')->assertOk();
});

it('only shows posts scoped by roadmap', function () {
    Post::factory()->count(6)->sequence(
        ['status' => 'pending'],
        ['status' => 'reviewing'],
        ['status' => 'planned'],
        ['status' => 'in progress'],
        ['status' => 'completed'],
        ['title' => 'Closed post', 'status' => 'closed'],
    )->create();

    get('/roadmap')
        ->assertOk()
        ->assertSee('Planned')
        ->assertSee('In Progress')
        ->assertDontSee('Completed')
        ->assertDontSee('Pending')
        ->assertDontSee('Reviewing')
        ->assertDontSee('Closed post');
});
