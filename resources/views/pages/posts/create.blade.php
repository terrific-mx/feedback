<?php

use App\Models\Board;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\WithFileUploads;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
    use WithFileUploads;

    public ?Collection $boards;
    public string $title = '';
    public string $description = '';
    public ?int $board = null;
    public array $images = [];

    public function mount()
    {
        $this->boards = Board::all();
    }

    public function create(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'board' => 'nullable|exists:boards,id',
            'images.*' => 'nullable|image|max:1024', // 1MB Max
        ]);

        $imagePaths = [];
        foreach ($this->images as $image) {
            $imagePaths[] = $image->store('post_images', 'public');
        }

        Auth::user()->posts()->create([
            'board_id' => $this->board,
            'title' => $this->title,
            'description' => $this->description,
            'image_paths' => $imagePaths,
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
                <flux:input wire:model="title" :label="__('Title')" required />
                <flux:textarea wire:model="description" :label="__('Description')" required />
                <flux:radio.group wire:model="board" :label="__('Board')" variant="cards" class="grid grid-cols-2">
                    @foreach ($boards as $board)
                        <flux:radio :value="$board->id" class="items-center">
                            <flux:badge :color="$board->color" variant="pill" inset="top bottom">{{ $board->name }}</flux:badge>
                            <flux:radio.indicator />
                        </flux:radio>
                    @endforeach
                </flux:radio.group>
                <div>
                    <flux:input type="file" wire:model="images" accept="image/*" multiple :label="__('Upload Images (Max 4)')" />
                    @error('images.*')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                </div>
            </form>
        </div>
    @endvolt
</x-layouts.board>
