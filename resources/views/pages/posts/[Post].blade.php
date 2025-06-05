<?php

use App\Models\Board;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Post $post;
    public ?Collection $comments = null;
    public ?Collection $boards = null;
    public bool $isSubscribed = false;

    public string $description = '';
    public string $status = 'pending';
    public ?string $board = null;

    public function mount()
    {
        $this->comments = $this->post->comments()->oldest()->get();
        $this->boards = Board::all();

        $this->status = $this->post->status;
        $this->board = $this->post->board?->id;

        $this->isSubscribed = Auth::check()
            && $this->post->subscribers()->where('user_id', Auth::id())->exists();
    }

    public function toggleSubscription()
    {
        $this->authorize('subscribe', $this->post);

        if ($this->isSubscribed) {
            $this->post->subscribers()->detach(Auth::id());
        } else {
            $this->post->subscribers()->attach(Auth::id());
        }

        $this->isSubscribed = !$this->isSubscribed;
    }

    public function comment(): void
    {
        $this->authorize('addComment', $this->post);
        $this->validate([
            'description' => 'required|string',
        ]);

        $comment = $this->post->comments()->create([
            'description' => $this->description,
            'user_id' => Auth::id(),
        ]);

        $this->post->notifySubscribers($comment);
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

<x-layouts.board :title="$post->title">
    @volt('pages.posts.show')
        <div class="max-sm:pt-8 max-sm:pb-16 pt-12 pb-24">
            <div class="mx-auto max-w-2xl px-6 sm:px-8">
                <div class="space-y-4">
                    <flux:button
                        :href="url()->previous() !== url()->current() ? url()->previous() : '/'"
                        icon="arrow-left"
                        icon:variant="micro"
                        size="sm"
                        class="rounded-full!"
                        square
                        wire:navigate />
                    <flux:card class="p-3 sm:p-4">
                        <div class="space-y-4">
                            <div class="flex justify-between flex-wrap gap-y-1">
                                <flux:heading size="lg" variant="strong">{{ $post->title }}</flux:heading>
                                <div class="flex gap-2">
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
                                    <flux:badge size="sm" :color="$post->board?->color ?? 'zinc'">
                                        {{ $post->board?->name ?? __('No Board') }}
                                    </flux:badge>
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
                                    @auth
                                        <flux:tooltip :content="$isSubscribed ? __('Unsubscribe from notifications') : __('Subscribe to notifications')">
                                            <flux:button
                                                wire:click="{{ $isSubscribed ? 'unsubscribe' : 'subscribe' }}"
                                                size="xs"
                                                :icon="$isSubscribed ? 'bell' : 'bell-slash'"
                                                :variant="$isSubscribed ? 'filled' : 'ghost'"
                                                square
                                            />
                                        </flux:tooltip>
                                    @endauth
                                </div>
                            </div>
                            <flux:text variant="strong" class="line-clamp-1">{{ $post->description }}</flux:text>
                            @if (count($post->image_urls) > 0)
                                <div class="grid grid-cols-4 gap-4 mt-4">
                                    @foreach ($post->image_urls as $url)
                                        <a href="{{ $url }}" target="_blank">
                                            <img src="{{ $url }}" alt="{{ __('Image :index for :title', ['index' => $loop->index, 'title' => $post->title]) }}" class="rounded-lg object-cover w-full aspect-square">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            <div class="flex justify-between flex-wrap gap-y-1">
                                <div class="flex gap-3 items-center">
                                    @if ($post->user->isAdmin())
                                    <flux:avatar :src="$post->user->avatar" size="sm" class="shrink-0" badge:circle badge:color="yellow">
                                        <x-slot:badge>
                                            <flux:icon.star variant="micro" class="w-2 text-white" />
                                        </x-slot:badge>
                                    </flux:avatar>
                                    @else
                                    <flux:avatar :src="$post->user->avatar" size="sm" class="shrink-0" />
                                    @endif
                                    <flux:heading>{{ $post->user->name }}</flux:heading>
                                </div>
                                <div class="flex gap-2 flex-row items-center">
                                    <flux:text class="text-xs">{{ $post->created_at->diffForHumans() }}</flux:text>
                                    <livewire:vote-button :$post :key="'vote-button-'.$post->id" />
                                    <flux:tooltip :content="__('Comments')">
                                        <flux:badge size="sm" icon="chat-bubble-left-right" class="font-mono tabular-nums">{{ $post->comments->count() }}</flux:badge>
                                    </flux:tooltip>
                                </div>
                            </div>
                            <form wire:submit="comment" class="space-y-4">
                                <flux:textarea wire:model="description" :placeholder="__('Add a comment...')" rows="3" required />
                                <div class="flex justify-end">
                                    <flux:button type="submit" variant="primary" size="sm">{{ __('Comment') }}</flux:button>
                                </div>
                            </form>
                            @foreach ($comments as $comment)
                                <flux:separator variant="subtle" />
                                <div class="space-y-4">
                                    <flux:text variant="strong">{{ $comment->description }}</flux:text>
                                    <div class="flex justify-between">
                                        <div class="flex gap-3 items-center">
                                            @if ($comment->user->isAdmin())
                                            <flux:avatar :src="$comment->user->avatar" size="xs" class="shrink-0" badge:circle badge:color="yellow">
                                                <x-slot:badge>
                                                    <flux:icon.star variant="micro" class="w-2 text-white" />
                                                </x-slot:badge>
                                            </flux:avatar>
                                            @else
                                            <flux:avatar :src="$comment->user->avatar" size="xs" class="shrink-0" />
                                            @endif
                                            <flux:heading>{{ $comment->user->name }}</flux:heading>
                                        </div>
                                        <div class="flex gap-2 flex-row items-center">
                                            <flux:text class="text-xs">{{ $comment->created_at->diffForHumans() }}</flux:text>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                </div>
            </div>
        </div>
    @endvolt
</x-layouts.board>
