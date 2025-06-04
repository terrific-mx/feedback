<?php

use App\Models\Board;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public string $board = 'all';
    public string $sort = 'top';

    #[Computed]
    public function boards(): Collection
    {
        return Board::all();
    }

    #[Computed]
    public function posts(): Collection
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

        return $query->get();
    }

    #[Computed]
    public function currentBoard(): ?Board
    {
        return $this->board === 'all'
            ? null
            : $this->boards->firstWhere('id', $this->board);
    }

    public function updatedBoard($board)
    {
        $this->validate(['board' => 'exists:boards,id']);
    }
}; ?>

<x-layouts.board>
    @volt('pages.index')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex items-baseline gap-3">
                        <flux:heading size="lg" class="text-lg">
                            @if ($this->currentBoard)
                            {{ $this->currentBoard->name }}
                            @else
                            {{ __('All feedback') }}
                            @endif
                        </flux:heading>
                        <flux:text>{{ $this->posts->count() }}</flux:text>
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
                            @foreach ($this->boards as $board)
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
            <div id="posts" class="mx-auto max-w-7xl px-6 sm:px-8 grid gap-4">
                @foreach ($this->posts as $post)
                    @include('partials.post', ['post' => $post])
                @endforeach
            </div>
        </div>
    @endvolt
</x-layouts.board>
