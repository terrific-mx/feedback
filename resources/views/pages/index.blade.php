<?php

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public ?Collection $posts = null;

    public function mount()
    {
        $this->posts = Post::all();
    }
}; ?>

<x-layouts.board>
    @volt('pages.index')
        <div>
            <flux:heading size="xl">{{ __('Posts') }}</flux:heading>
            <flux:button href="/posts/create" class="mt-2" variant="primary">{{ __('Create Post') }}</flux:button>
            <ul class="mt-8">
                @foreach ($posts as $post)
                    <li>
                        <flux:link href="/posts/{{ $post->id }}">
                            {{ $post->title }}
                        </flux:link>
                    </li>
                @endforeach
            </ul>
        </div>
    @endvolt
</x-layouts.board>
