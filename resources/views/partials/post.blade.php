<flux:card wire:key="{{ $post->id }}" size="sm" class="p-3 sm:p-4 relative hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
    <div class="space-y-4">
        <div class="flex justify-between flex-wrap gap-y-1flex-wrap gap-y-1">
            <flux:heading size="lg" variant="strong">{{ $post->title }}</flux:heading>
            <flux:badge size="sm" :color="$post->status_color">{{ $post->formatted_status }}</flux:badge>
        </div>
        <flux:text variant="strong" class="line-clamp-1">{{ $post->description }}</flux:text>
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
                <flux:tooltip :content="__('Comments')" class="relative z-10">
                    <flux:badge size="sm" icon="chat-bubble-left-right" class="font-mono tabular-nums">{{ $post->comments->count() }}</flux:badge>
                </flux:tooltip>
            </div>
            <a href="/posts/{{ $post->id }}" class="absolute inset-0"></a>
        </div>
    </div>
</flux:card>
