<?php

use App\Models\Post;
use Livewire\Volt\Component;

new class extends Component {
    public Post $post;
}; ?>

<x-layouts.board>
    @volt('pages.posts.show')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex items-baseline gap-3">
                        <flux:heading size="lg" class="text-lg">{{ __('Post') }}</flux:heading>
                    </div>
                </div>
            </div>
            <div class="min-h-4 sm:min-h-10"></div>
            <div id="post" class="mx-auto max-w-lg max-sm:px-2">
                <div class="p-3 sm:p-4 rounded-lg">
                    <div>
                        <flux:heading size="lg" variant="strong">{{ $post->title }}</flux:heading>
                        <div class="min-h-2"></div>
                        <flux:text variant="strong">{{ $post->description }}</flux:text>
                    </div>
                    <div class="min-h-2 sm:min-h-4"></div>
                    <div class="flex flex-row sm:items-center gap-2">
                        <flux:avatar :src="$post->user->avatar" size="xs" class="shrink-0" />
                        <div class="flex flex-col gap-0.5 sm:gap-2 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-2">
                                <flux:heading>{{ $post->user->name }}</flux:heading>
                            </div>
                            <flux:text class="text-sm">{{ $post->created_at->diffForHumans() }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.board>
