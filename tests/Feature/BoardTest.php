<?php

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

describe('index', function () {
    it('renders the component with boards', function () {
        Board::factory()->count(3)->sequence(
            ['name' => 'Board 1'],
            ['name' => 'Board 2'],
            ['name' => 'Board 3'],
        )->create();
        $this->actingAs(User::factory()->create());

        $response = $this->get('/boards');

        $response->assertSee('Board 1');
        $response->assertSee('Board 2');
        $response->assertSee('Board 3');
    });

    it('allows deletion of a board if authorized', function () {
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);
        $board = Board::factory()->create();
        $this->actingAs($adminUser);

        Volt::actingAs($adminUser)->test('pages.boards.index')
            ->call('delete', $board->id);

        assertDatabaseMissing('boards', ['id' => $board->id]);
    });

    it('denies deletion of a board if unauthorized', function () {
        $user = User::factory()->create();
        $board = Board::factory()->create();
        $this->actingAs($user);

        $component = Volt::actingAs($user)->test('pages.boards.index')
            ->call('delete', $board->id);

        $component->assertForbidden();
    });
});

describe('create', function () {
    it('renders the create form for admin users', function () {
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);

        actingAs($adminUser)->get('/boards/create')->assertSuccessful();
    });

    it('denies access to create form for non-admin users', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);

        actingAs($user)->get('/boards/create')->assertForbidden();
    });

    it('validates the name field is required', function () {
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);

        $component = Volt::actingAs($adminUser)->test('pages.boards.create')
            ->set('name', '')
            ->set('color', 'Zinc')
            ->call('create');

        $component->assertHasErrors(['name' => 'required']);
    });

    it('validates the color field is required and valid', function () {
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);

        $component = Volt::actingAs($adminUser)->test('pages.boards.create')
            ->set('name', 'Test Board')
            ->set('color', '')
            ->call('create');

        $component->assertHasErrors(['color' => 'required']);

        $component->set('color', 'InvalidColor')
            ->call('create');

        $component->assertHasErrors(['color' => 'in']);
    });

    it('creates a board successfully with valid data', function () {
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);

        Volt::actingAs($adminUser)->test('pages.boards.create')
            ->set('name', 'Test Board')
            ->set('color', 'zinc')
            ->call('create');

        $this->assertDatabaseHas('boards', [
            'name' => 'Test Board',
            'color' => 'zinc',
        ]);
    });

    it('redirects to the boards index after creation', function () {
        $adminUser = User::factory()->create(['email' => config('feedback.admin_emails')[0]]);

        $component = Volt::actingAs($adminUser)->test('pages.boards.create')
            ->set('name', 'Test Board')
            ->set('color', 'zinc')
            ->call('create');

        $component->assertRedirect('/boards');
    });
});
