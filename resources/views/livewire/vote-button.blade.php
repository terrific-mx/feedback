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

        if ($this->post->hasVoted(auth()->user())) {
            $this->post->votes()->where('user_id', auth()->id())->delete();
        } else {
            $this->post->votes()->create(['user_id' => auth()->id()]);
        }

        $this->post->refresh();

        $this->dispatch('post-votes-updated');
    }
}; ?>

<div class="flex flex-col items-center">
    <button type="button" wire:click="upvote" class="group flex items-center rounded-full p-2 transition-colors duration-200 hover:bg-zinc-200 dark:hover:bg-zinc-700/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:focus:ring-offset-zinc-800">
        <svg class="h-5 w-5 text-zinc-400 group-hover:text-red-500 transition-colors duration-200 {{ (auth()->user() && $post->hasVoted(auth()->user())) ? 'text-red-500' : '' }}" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.707-10.293a1 1 0 00-1.414 1.414L8.586 10H6a1 1 0 100 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10h2.586a1 1 0 100-2h-2.586l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586z" clip-rule="evenodd"></path>
        </svg>
    </button>
    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ $this->votes }}</span>
</div>
