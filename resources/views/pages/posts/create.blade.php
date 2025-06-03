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
    public array $pendingImages = [];
    public array $images = [];

    public function mount()
    {
        $this->boards = Board::all();
    }

    public function updatedImages(): void
    {
        $this->validate([
            'images.*' => 'nullable|image|max:5120', // 5MB Max
        ]);

        $newImages = collect($this->images)->take(4 - count($this->pendingImages));

        $this->pendingImages = array_merge($this->pendingImages, $newImages->all());

        $this->reset('images');
    }

    public function create(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'board' => 'nullable|exists:boards,id',
            'pendingImages' => 'nullable|array|max:4',
            'pendingImages.*' => 'nullable|image|max:5120', // 5MB Max
        ]);

        $imagePaths = [];
        foreach ($this->pendingImages as $image) {
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
                <div x-data="{ isUploading: false, progress: 0 }"
                    x-on:livewire-upload-start="isUploading = true"
                    x-on:livewire-upload-finish="isUploading = false"
                    x-on:livewire-upload-error="isUploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress">
                    <flux:input type="file" wire:model="images" accept="image/*" multiple :label="__('Upload Images')" :badge="__('Max 4')" />
                    <div x-show="isUploading">
                        <flux:text x-text="`{{ __('Uploading images:') }} ${progress}%`" class="mt-2"></flux:text>
                    </div>
                </div>
                @if (count($pendingImages) > 0)
                <div class="grid grid-cols-4 gap-4 mt-4">
                    @foreach ($pendingImages as $image)
                        @if ($image->isPreviewable())
                        <img src="{{ $image->temporaryUrl() }}" alt="{{ __('Image :index', ['index' => $loop->index]) }}" class="rounded-lg object-cover w-full aspect-square">
                        @endif
                    @endforeach
                </div>
                @endif
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                </div>
            </form>
        </div>
    @endvolt
</x-layouts.board>
