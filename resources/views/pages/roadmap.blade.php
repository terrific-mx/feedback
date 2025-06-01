<?php

use App\Models\Board;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public ?Collection $posts = null;

    public function mount()
    {
        $this->posts = Post::roadmap()->latest()->get();
    }
}; ?>

<x-layouts.board>
    @volt('pages.roadmap')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex items-baseline gap-3">
                        <flux:heading size="lg" class="text-lg">{{ __('Roadmap') }}</flux:heading>
                    </div>

                    <flux:spacer />

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
