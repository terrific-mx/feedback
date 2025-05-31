<?php

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

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

});
