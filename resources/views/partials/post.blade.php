<div class="p-3 sm:p-4 rounded-lg relative hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
    <div>
        <flux:heading size="lg" variant="strong">{{ $post->title }}</flux:heading>
        <div class="min-h-2"></div>
        <flux:text variant="strong">{{ $post->description }}</flux:text>
    </div>
    <div class="min-h-2 sm:min-h-4"></div>
    <div class="flex flex-row sm:items-center gap-2">
        @if ($post->user->isAdmin())
        <flux:avatar :src="$post->user->avatar" size="xs" class="shrink-0" badge:circle badge:color="yellow">
            <x-slot:badge>
                <flux:icon.star variant="micro" class="w-2 text-white" />
            </x-slot:badge>
        </flux:avatar>
        @else
        <flux:avatar :src="$post->user->avatar" size="xs" class="shrink-0" />
        @endif
        <div class="flex flex-col gap-0.5 sm:gap-2 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2">
                <flux:heading>{{ $post->user->name }}</flux:heading>
            </div>
            <flux:text class="text-sm">{{ $post->created_at->diffForHumans() }}</flux:text>
            <flux:badge size="sm" :color="$post->status_color">{{ $post->formatted_status }}</flux:badge>
            <livewire:vote-button :$post />
            <flux:tooltip :content="__('Comments')" class="relative z-10">
                <flux:badge size="sm" icon="chat-bubble-left-right" class="font-mono tabular-nums">{{ $post->comments->count() }}</flux:badge>
            </flux:tooltip>
        </div>
        <a href="/posts/{{ $post->id }}" class="absolute inset-0"></a>
    </div>
</div>
