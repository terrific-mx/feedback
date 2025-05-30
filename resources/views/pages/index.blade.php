<?php

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public ?Collection $posts = null;

    public function mount()
    {
        $this->posts = Post::latest()->get();
    }
}; ?>

<x-layouts.board>
    @volt('pages.index')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex items-baseline gap-3">
                        <flux:heading size="lg" class="text-lg">{{ __('All feedback') }}</flux:heading>
                    </div>
                    <flux:spacer />
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
                                <flux:badge size="sm" color="yellow">{{ $post->formatted_status }}</flux:badge>
                            </div>
                            <a href="/posts/{{ $post->id }}" class="absolute inset-0"></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endvolt
</x-layouts.board>
