<?php

use App\Models\Board;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public string $board = 'all';
    public string $sort = 'top';
    public string $filter = 'open';

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

        match ($this->filter) {
            'open' => $query->open(),
            'closed' => $query->closed(),
            default => $query->open(),
        };

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

    public function updatedFilter($filter)
    {
        $this->validate(['filter' => ['in:open,closed']]);
    }
}; ?>

<x-layouts.board :title="__('Posts')">
    @volt('pages.index')
        <div class="max-sm:pt-8 max-sm:pb-16 pt-12 pb-24">
            <div class="mx-auto max-w-2xl px-6 sm:px-8">
                <div class="space-y-5">
                    <div class="max-sm:block max-sm:space-y-4 flex justify-between">
                        <div>
                            <flux:heading size="xl">{{ __('All posts') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('All posts and ideas from our community.') }}</flux:text>
                        </div>
                        <flux:button href="/posts/create" icon="plus" variant="primary" wire:navigate>{{ __('New post') }}</flux:button>
                    </div>

                    <div class="max-sm:block flex">
                        <flux:radio.group wire:model.live="filter" variant="segmented">
                            <flux:radio value="open" :label="__('Open')" />
                            <flux:radio value="closed" :label="__('Closed')" />
                        </flux:radio.group>
                    </div>
                </div>
                <div class="min-h-8"></div>
                <div class="flex items-center gap-2 justify-end">
                    <flux:select wire:model.live="board" variant="listbox" class="sm:max-w-fit">
                        <x-slot name="trigger">
                            <flux:select.button>
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
                            <flux:select.button>
                                <flux:icon.arrows-up-down variant="micro" class="mr-2 text-zinc-400" />
                                <flux:select.selected />
                            </flux:select.button>
                        </x-slot>
                        <flux:select.option value="top" selected>{{ __('Top') }}</flux:select.option>
                        <flux:select.option value="newest">{{ __('Newest') }}</flux:select.option>
                    </flux:select>
                </div>

                <div class="min-h-6"></div>

                <div id="posts" class="grid gap-4">
                    @foreach ($this->posts as $post)
                        @include('partials.post', ['post' => $post])
                    @endforeach
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.board>
