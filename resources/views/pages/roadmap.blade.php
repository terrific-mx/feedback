<?php

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new class extends Component {
    public string $filter = 'open';

    #[Computed]
    public function plannedPosts(): Collection
    {
        return Post::planned()->latest('updated_at')->get();
    }

    #[Computed]
    public function inProgressPosts(): Collection
    {
        return Post::inProgress()->latest('updated_at')->get();
    }

    #[Computed]
    public function completedPosts(): Collection
    {
        return Post::completed()->latest('updated_at')->get();
    }
}; ?>

<x-layouts.board :title="__('Roadmap')">
    @volt('pages.roadmap')
        <div class="max-sm:pt-8 max-sm:pb-16 pt-12 pb-24">
            <div class="mx-auto max-w-2xl px-6 sm:px-8">
                <div class="space-y-5">
                    <div class="max-sm:block max-sm:space-y-4 flex justify-between">
                        <div>
                            <flux:heading size="xl">{{ __('Roadmap') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('Track the progress of your ideas and see what\'s coming next.') }}</flux:text>
                        </div>
                        <flux:button href="/posts/create" icon="plus" variant="primary">{{ __('New post') }}</flux:button>
                    </div>

                    <div class="max-sm:block flex">
                        <flux:radio.group wire:model.live="filter" variant="segmented">
                            <flux:radio value="open" :label="__('Open')" />
                            <flux:radio value="closed" :label="__('Closed')" />
                        </flux:radio.group>
                    </div>
                </div>

                @if($filter === 'open')
                    @if($this->inProgressPosts->isNotEmpty())
                    <div class="min-h-4 sm:min-h-10"></div>
                    <div id="inProgressPosts" class="grid gap-4">
                        @foreach ($this->inProgressPosts as $post)
                            @include('partials.post', ['post' => $post])
                        @endforeach
                    </div>
                    @endif

                    @if($this->plannedPosts->isNotEmpty())
                    <div class="min-h-4 sm:min-h-10"></div>
                    <div id="plannedPosts" class="grid gap-4">
                        @foreach ($this->plannedPosts as $post)
                            @include('partials.post', ['post' => $post])
                        @endforeach
                    </div>
                    @endif
                @else
                    @if($this->completedPosts->isNotEmpty())
                    <div class="min-h-4 sm:min-h-10"></div>
                    <div id="completedPosts" class="grid gap-4">
                        @foreach ($this->completedPosts as $post)
                            @include('partials.post', ['post' => $post])
                        @endforeach
                    </div>
                    @endif
                @endif
            </div>
        </div>
    @endvolt
</x-layouts.board>
