<?php

use App\Models\Board;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public ?Collection $posts = null;
    public ?Collection $boards = null;
    public ?Board $currentBoard = null;

    public string $board_filter = 'all';

    public function mount()
    {
        $this->boards = Board::all();
        $this->filterPosts();
    }

    public function updatedBoardFilter($board)
    {
        if ($this->board_filter !== 'all') {
            $this->validate(['board_filter' => 'exists:boards,id']);
            $this->currentBoard = Board::find($board);
        } else {
            $this->currentBoard = null;
        }

        $this->filterPosts();
    }

    protected function filterPosts()
    {
        $this->posts = $this->currentBoard
            ? Post::where('board_id', $this->board_filter)->latest()->get()
            : Post::latest()->get();
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
                    </div>
                    <flux:spacer />

                    <div class="flex items-center gap-2">
                        <flux:select wire:model.live="board_filter" variant="listbox" class="sm:max-w-fit">
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
                    </div>

                    <flux:button href="/posts/create" icon="pencil-square" size="sm" variant="primary">{{ __('New post') }}</flux:button>
                </div>
            </div>
            <div class="min-h-4 sm:min-h-10"></div>
            <div id="posts" class="mx-auto max-w-lg max-sm:px-2">
                @foreach ($posts as $post)
                    <div class="p-3 sm:p-4 rounded-lg relative hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                        <div>
                            <flux:heading size="lg" variant="strong">{{ $post->title }}</flux:heading>
                            <div class="min-h-2"></div>
                            <flux:text variant="strong">{{ $post->description }}</flux:text>
                        </div>
                        <div class="min-h-2 sm:min-h-4"></div>
                        <div class="flex flex-row sm:items-center gap-2">
                            @if ($post->user->isAdmin())
                            <flux:avatar :src="$post->user->avatar" size="xs" class="shrink-0" badge:circle badge:color="yellow">
                                <x-slot:badge>
                                    <flux:icon.star variant="micro" class="w-2 text-white" />
                                </x-slot:badge>
                            </flux:avatar>
                            @else
                            <flux:avatar :src="$post->user->avatar" size="xs" class="shrink-0" />
                            @endif
                            <div class="flex flex-col gap-0.5 sm:gap-2 sm:flex-row sm:items-center">
                                <div class="flex items-center gap-2">
                                    <flux:heading>{{ $post->user->name }}</flux:heading>
                                </div>
                                <flux:text class="text-sm">{{ $post->created_at->diffForHumans() }}</flux:text>
                                <flux:badge size="sm" :color="$post->status_color">{{ $post->formatted_status }}</flux:badge>
                                <flux:tooltip :content="__('Comments')" class="relative z-10">
                                    <flux:badge size="sm" icon="chat-bubble-left-right" class="font-mono tabular-nums">{{ $post->comments->count() }}</flux:badge>
                                </flux:tooltip>
                            </div>
                            <a href="/posts/{{ $post->id }}" class="absolute inset-0"></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endvolt
</x-layouts.board>
