<?php

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public ?Collection $plannedPosts = null;
    public ?Collection $inProgressPosts = null;
    public ?Collection $completedPosts = null;
    public string $filter = 'open';

    public function mount()
    {
        $query = Post::query();

        match ($this->filter) {
            'open' => $query->open(),
            'closed' => $query->closed(),
            default => $query->open(),
        };

        $this->plannedPosts = (clone $query)->planned()->latest('updated_at')->get();
        $this->inProgressPosts = (clone $query)->inProgress()->latest('updated_at')->get();
        $this->completedPosts = (clone $query)->completed()->latest('updated_at')->get();
    }

    public function updatedFilter($filter)
    {
        $this->validate(['filter' => ['in:open,closed']]);
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

            <div class="flex">
                <flux:radio.group wire:model.live="filter" variant="segmented">
                    <flux:radio value="open" :label="__('Open')" />
                    <flux:radio value="closed" :label="__('Closed')" />
                </flux:radio.group>
            </div>

            @if($inProgressPosts->isNotEmpty())
            <div class="min-h-4 sm:min-h-10"></div>
            <div id="inProgressPosts" class="mx-auto max-w-lg max-sm:px-2">
                @foreach ($inProgressPosts as $post)
                    @include('partials.post', ['post' => $post])
                @endforeach
            </div>
            @endif

            @if($plannedPosts->isNotEmpty())
            <div class="min-h-4 sm:min-h-10"></div>
            <div id="plannedPosts" class="mx-auto max-w-lg max-sm:px-2">
                @foreach ($plannedPosts as $post)
                    @include('partials.post', ['post' => $post])
                @endforeach
            </div>
            @endif

            @if($completedPosts->isNotEmpty())
            <div class="min-h-4 sm:min-h-10"></div>
            <div id="completedPosts" class="mx-auto max-w-lg max-sm:px-2">
                @foreach ($completedPosts as $post)
                    @include('partials.post', ['post' => $post])
                @endforeach
            </div>
            @endif
        </div>
    @endvolt
</x-layouts.board>
