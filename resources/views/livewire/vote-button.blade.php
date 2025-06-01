<?php

use App\Models\Post;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

new class extends Component {
    public Post $post;

    #[Computed]
    #[On('post-votes-updated')]
    public function votes(): int
    {
        return $this->post->votes()->count();
    }

    public function upvote(): void
    {
        $this->authorize('vote', $this->post);

        $this->post->toggleVote(auth()->user());

        $this->post->refresh();

        $this->dispatch('post-votes-updated');
    }
}; ?>

<flux:tooltip :content="__('Votes')" class="relative z-10">
    <form wire:submit="upvote">
        @can('vote', $this->post)
        <flux:badge as="button" type="submit" :color="$post->hasVoted(auth()->user()) ? 'pink' : 'zinc'" size="sm" icon="hand-thumb-up" class="font-mono tabular-nums">
            {{ $this->votes }}
        </flux:badge>
        @else
        <flux:badge size="sm" icon="hand-thumb-up" class="font-mono tabular-nums">
            {{ $this->votes }}
        </flux:badge>
        @endcan
    </form>
</flux:tooltip>
