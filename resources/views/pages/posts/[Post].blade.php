<?php

use App\Models\Board;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    public Post $post;
    public ?Collection $comments = null;
    public ?Collection $boards = null;

    public string $description = '';
    public string $status = 'pending';
    public ?string $board = null;

    public function mount()
    {
        $this->comments = $this->post->comments()->oldest()->get();
        $this->boards = Board::all();

        $this->status = $this->post->status;
        $this->board = $this->post->board?->id;
    }

    public function comment(): void
    {
        $this->authorize('addComment', $this->post);

        $this->validate([
            'description' => 'required|string',
        ]);

        $this->post->comments()->create([
            'description' => $this->description,
            'user_id' => Auth::id(),
        ]);

        $this->reset('description');

        $this->comments = $this->post->comments()->oldest()->get();
    }

    public function changeStatus()
    {
        $this->authorize('updateStatus', $this->post);

        $this->validate([
            'status' => 'required|in:pending,reviewing,planned,in progress,completed,closed',
        ]);

        $this->post->update(['status' => $this->status]);

        $this->post->refresh();
    }

    public function changeBoard()
    {
        $this->authorize('updateBoard', $this->post);

        $this->validate([
            'board' => 'required|exists:boards,id',
        ]);

        $this->post->update(['board_id' => $this->board]);

        $this->post->refresh();
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
                    @if (count($post->image_urls) > 0)
                        <div class="min-h-2 sm:min-h-4"></div>
                        <div class="grid grid-cols-4 gap-4 mt-4">
                            @foreach ($post->image_urls as $url)
                                <a href="{{ $url }}" target="_blank">
                                    <img src="{{ $url }}" alt="{{ __('Image :index for :title', ['index' => $loop->index, 'title' => $post->title]) }}" class="rounded-lg object-cover w-full aspect-square">
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div class="min-h-2 sm:min-h-4"></div>
                    <div class="flex flex-row sm:items-center gap-2">
                        <flux:avatar :src="$post->user->avatar" size="xs" class="shrink-0" />
                        <div class="flex flex-col gap-0.5 sm:gap-2 sm:flex-row sm:items-center">
                            <div class="flex items-center gap-2">
                                <flux:heading>{{ $post->user->name }}</flux:heading>
                            </div>
                            <flux:text class="text-sm">{{ $post->created_at->diffForHumans() }}</flux:text>
                            @can('updateBoard', $post)
                            <flux:dropdown>
                                <flux:badge as="button" :color="$post->board?->color ?? 'zinc'" icon:trailing="chevron-down" size="sm">
                                    {{ $post->board?->name ?? __('No Board') }}
                                </flux:badge>

                                <flux:menu>
                                    <flux:menu.radio.group wire:change="changeBoard" wire:model="board">
                                        @foreach ($boards as $board)
                                        <flux:menu.radio :value="$board->id">{{ $board->name }}</flux:menu.radio>
                                        @endforeach
                                    </flux:menu.radio.group>
                                </flux:menu>
                            </flux:dropdown>
                            @elseif ($post->board)
                            <flux:badge size="sm" :color="$post->status_color">{{ $post->formatted_status }}</flux:badge>
                            @endcan
                            @can('updateStatus', $post)
                            <flux:dropdown>
                                <flux:badge as="button" :color="$post->status_color" icon:trailing="chevron-down" size="sm">
                                    {{ $post->formatted_status }}
                                </flux:badge>

                                <flux:menu>
                                    <flux:menu.radio.group wire:change="changeStatus" wire:model="status">
                                        <flux:menu.radio value="pending">{{ __('Pending') }}</flux:menu.radio>
                                        <flux:menu.radio value="reviewing">{{ __('Reviewing') }}</flux:menu.radio>
                                        <flux:menu.radio value="planned">{{ __('Planned') }}</flux:menu.radio>
                                        <flux:menu.radio value="in progress">{{ __('In Progress') }}</flux:menu.radio>
                                        <flux:menu.radio value="completed">{{ __('Completed') }}</flux:menu.radio>
                                        <flux:menu.radio value="closed">{{ __('Closed') }}</flux:menu.radio>
                                    </flux:menu.radio.group>
                                </flux:menu>
                            </flux:dropdown>
                            @else
                            <flux:badge size="sm" :color="$post->status_color">{{ $post->formatted_status }}</flux:badge>
                            @endcan
                            <livewire:vote-button :$post :key="'vote-button-'.$post->id" />
                            <flux:tooltip :content="__('Comments')">
                                <flux:badge size="sm" icon="chat-bubble-left-right" class="font-mono tabular-nums">{{ $post->comments->count() }}</flux:badge>
                            </flux:tooltip>
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
