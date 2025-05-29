<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
    public string $title = '';
    public string $description = '';

    public function create(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Auth::user()->posts()->create([
            'title' => $this->title,
            'description' => $this->description,
        ]);

        $this->redirect('/');
    }
}; ?>

<x-layouts.board>
    @volt('pages.posts.create')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex">
                        <flux:heading size="lg" class="text-lg">{{ __('New post') }}</flux:heading>
                    </div>
                </div>
            </div>
            <div class="min-h-4 sm:min-h-10"></div>
            <form id="form" wire:submit="create" class="mx-auto max-w-lg max-sm:px-2 space-y-6">
                <flux:input wire:model="title" label="Title" />
                <flux:textarea wire:model="description" label="Description" />
                <flux:button type="submit" variant="primary">Create</flux:button>
            </form>
        </div>
    @endvolt
</x-layouts.board>
