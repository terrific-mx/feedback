<?php

use App\Models\Board;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public ?Collection $posts = null;
    public ?Collection $boards = null;
    public ?Board $currentBoard = null;

    public string $board = 'all';
    public string $sort = 'top';

    public function mount()
    {
        $this->loadBoards();
        $this->applyFiltersAndSorting();
    }

    public function updatedBoard($board)
    {
        $this->resetCurrentBoard();
        $this->validateBoardFilter();
        $this->applyFiltersAndSorting();
    }

    public function updatedSort($sort)
    {
        $this->applyFiltersAndSorting();
    }

    protected function loadBoards()
    {
        $this->boards = Board::all();
    }

    protected function resetCurrentBoard()
    {
        $this->currentBoard = null;
    }

    protected function validateBoardFilter()
    {
        if ($this->board !== 'all') {
            $this->validate(['board' => 'exists:boards,id']);
            $this->currentBoard = $this->boards->firstWhere('id', $this->board);
        }
    }

    protected function applyFiltersAndSorting()
    {
        $query = Post::query();

        if ($this->board !== 'all') {
            $query->byBoard($this->currentBoard);
        }

        match ($this->sort) {
            'top' => $query->top(),
            'newest' => $query->latest(),
            default => $query->latest(),
        };

        $this->posts = $query->get();
    }
}; ?>

<x-layouts.board>
    @volt('pages.index')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex items-baseline gap-3">
                        <flux:heading size="lg" class="text-lg">
                            @if ($currentBoard)
                            {{ $currentBoard->name }}
                            @else
                            {{ __('All feedback') }}
                            @endif
                        </flux:heading>
                        <flux:text>{{ $posts->count() }}</flux:text>
                    </div>
                    <flux:spacer />

                    <div class="flex items-center gap-2">
                        <flux:select wire:model.live="board" variant="listbox" class="sm:max-w-fit">
                            <x-slot name="trigger">
                                <flux:select.button size="sm">
                                    <flux:icon.funnel variant="micro" class="mr-2 text-zinc-400" />
                                    <flux:select.selected />
                                </flux:select.button>
                            </x-slot>
                            <flux:select.option value="all" selected>{{ __('All') }}</flux:select.option>
                            @foreach ($boards as $board)
                            <flux:select.option :value="$board->id">{{ $board->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="sort" variant="listbox" class="sm:max-w-fit">
                            <x-slot name="trigger">
                                <flux:select.button size="sm">
                                    <flux:icon.arrows-up-down variant="micro" class="mr-2 text-zinc-400" />
                                    <flux:select.selected />
                                </flux:select.button>
                            </x-slot>
                            <flux:select.option value="top" selected>{{ __('Top') }}</flux:select.option>
                            <flux:select.option value="newest">{{ __('Newest') }}</flux:select.option>
                        </flux:select>
                    </div>

                    <flux:button href="/posts/create" icon="pencil-square" size="sm" variant="primary">{{ __('New post') }}</flux:button>
                </div>
            </div>
            <div class="min-h-4 sm:min-h-10"></div>
            <div id="posts" class="mx-auto max-w-lg max-sm:px-2">
                @foreach ($posts as $post)
                    @include('partials.post', ['post' => $post])
                @endforeach
            </div>
        </div>
    @endvolt
</x-layouts.board>
