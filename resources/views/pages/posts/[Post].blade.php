<?php

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    public Post $post;
    public ?Collection $comments = null;

    #[Validate('required|string')]
    public string $description = '';

    public function mount()
    {
        $this->comments = $this->post->comments()->oldest()->get();
    }

    public function comment(): void
    {
        $this->authorize('addComment', $this->post);

        $this->validate();

        $this->post->comments()->create([
            'description' => $this->description,
            'user_id' => Auth::id(),
        ]);

        $this->reset('description');

        $this->comments = $this->post->comments()->oldest()->get();
    }
}; ?>

<x-layouts.board>
    @volt('pages.posts.show')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex items-baseline gap-3">
                        <flux:heading size="lg" class="text-lg">{{ __('Submission') }}</flux:heading>
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
                    <div class="min-h-2 sm:min-h-4"></div>
                    <form wire:submit="comment">
                        <flux:textarea wire:model="description" :placeholder="__('Add a comment...')" rows="3" required />
                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" size="sm" class="mt-2">{{ __('Comment') }}</flux:button>
                        </div>
                    </form>
                </div>
                <div class="min-h-4 sm:min-h-2"></div>
                @foreach ($comments as $comment)
                    <div class="p-3 sm:p-4 rounded-lg:bg:bg-zinc-50 dark:hover:bg-zinc-700/50">
                        <flux:text variant="strong">{{ $comment->description }}</flux:text>
                        <div class="min-h-2 sm:min-h-4"></div>
                        <div class="flex flex-row sm:items-center gap-2">
                            @if ($comment->user->isAdmin())
                            <flux:avatar :src="$comment->user->avatar" size="xs" class="shrink-0" badge:circle badge:color="yellow">
                                <x-slot:badge>
                                    <flux:icon.star variant="micro" class="w-2 text-white" />
                                </x-slot:badge>
                            </flux:avatar>
                            @else
                            <flux:avatar :src="$comment->user->avatar" size="xs" class="shrink-0" />
                            @endif
                            <div class="flex flex-col gap-0.5 sm:gap-2 sm:flex-row sm:items-center">
                                <div class="flex items-center gap-2">
                                    <flux:heading>{{ $comment->user->name }}</flux:heading>
                                </div>
                                <flux:text class="text-sm">{{ $comment->created_at->diffForHumans() }}</flux:text>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endvolt
</x-layouts.board>
