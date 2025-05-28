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
        <form wire:submit="create" class="space-y-6">
            <flux:input wire:model="title" label="Title" />
            <flux:textarea wire:model="description" label="Description" />
            <flux:button type="submit" variant="primary">Create</flux:button>
        </form>
    @endvolt
</x-layouts.board>
