<?php

use App\Models\Board;
use Illuminate\Database\Eloquent\Collection;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
    public ?Collection $boards = null;

    public function mount()
    {
        $this->boards = Board::all();
    }

    public function delete(Board $board): void
    {
        $this->authorize('delete', $board);

        $board->delete();

        $this->boards = Board::all();
    }
} ?>

<x-layouts.board>
    @volt('pages.boards.index')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex items-baseline gap-3">
                        <flux:heading size="lg" class="text-lg">{{ __('Boards') }}</flux:heading>
                    </div>
                    <flux:spacer />
                    <flux:button href="/boards/create" icon="pencil-square" size="sm" variant="primary" wire:navigate>{{ __('New board') }}</flux:button>
                </div>
            </div>
            <div class="min-h-4 sm:min-h-10"></div>
            @if($boards->isNotEmpty())
            <div class="mx-auto max-w-lg max-sm:px-2">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                    </flux:table.columns>
                    @foreach ($boards as $board)
                    <flux:table.rows>
                        <flux:table.row>
                            <flux:table.cell>
                                <flux:badge :color="$board->color" variant="pill">{{ $board->name }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:dropdown align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" inset="top bottom"></flux:button>

                                    <flux:menu>
                                        <form wire:submit="delete({{ $board->id }})" wire:confirm="{{ __('Are you sure?') }}">
                                            <flux:menu.item type="submit" variant="danger" icon="trash">{{ __('Delete') }}</flux:menu.item>
                                        </form>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                    @endforeach
                </flux:table>
            </div>
            @endif
        </div>
    @endvolt
</x-layouts.board>
