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
    public array $images = [];

    public ?int $board = null;
    public string $title = '';
    public string $description = '';
    public array $pendingImages = [];

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

        $post = Auth::user()->posts()->create([
            'board_id' => $this->board,
            'title' => $this->title,
            'description' => $this->description,
            'image_paths' => $imagePaths,
        ]);

        $this->redirect("/posts/{$post->id}");
    }
}; ?>

<x-layouts.board :title="__('Share Your Feedback')">
    @volt('pages.posts.create')
        <div class="max-sm:pt-8 max-sm:pb-16 pt-12 pb-24">
            <div class="mx-auto max-w-2xl px-6 sm:px-8">
                <div>
                    <flux:heading size="xl">{{ __('Share Your Feedback') }}</flux:heading>
                    <flux:separator variant="subtle" class="mt-4 max-sm:hidden" />
                </div>
                <div class="min-h-8"></div>
                <form id="form" wire:submit="create">
                    <flux:input wire:model="title" :label="__('Feedback Title')" required />
                    <flux:separator variant="subtle" class="my-5" />
                    <flux:textarea wire:model="description" :label="__('Detailed Description')" required />
                    <flux:separator variant="subtle" class="my-5" />
                    <flux:radio.group wire:model="board" :label="__('Select a Board')" variant="cards" class="grid grid-cols-2">
                        @foreach ($boards as $board)
                            <flux:radio :value="$board->id" class="items-center">
                                <flux:badge :color="$board->color" size="sm" variant="pill" inset="top bottom">{{ $board->name }}</flux:badge>
                                <flux:radio.indicator />
                            </flux:radio>
                        @endforeach
                    </flux:radio.group>
                    <flux:separator variant="subtle" class="my-5" />
                    <div x-data="{ isUploading: false, progress: 0 }"
                        x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false"
                        x-on:livewire-upload-error="isUploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress">
                        <flux:input type="file" wire:model="images" accept="image/*" multiple :label="__('Attach Images')" :description="__('You can upload up to 4 images.')"  :badge="__('Optional')" />
                        <div x-show="isUploading">
                            <flux:text x-text="`{{ __('Uploading images:') }} ${progress}%`" class="mt-2"></flux:text>
                        </div>
                    </div>
                    @if (count($pendingImages) > 0)
                    <div class="grid grid-cols-4 gap-4 mt-5">
                        @foreach ($pendingImages as $image)
                            @if ($image->isPreviewable())
                            <flux:card class="relative p-0!" wire:key="pending-image-{{ $loop->index }}">
                                <div  class="absolute -top-2 -right-2">
                                    <flux:button x-on:click="$wire.removeUpload('pendingImages', '{{ $image->getFilename() }}')" variant="primary" size="xs" icon="x-mark" />
                                </div>
                                <img src="{{ $image->temporaryUrl() }}" alt="{{ __('Image :index', ['index' => $loop->index]) }}" class="rounded-xl object-cover w-full aspect-square">
                            </flux:card>

                            @endif
                        @endforeach
                    </div>
                    @endif
                    <flux:separator variant="subtle" class="mt-6 mb-5" />
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-x-4">
                        @if(url()->previous() !== url()->current())
                            <flux:button :href="url()->previous()">{{ __('Cancel') }}</flux:button>
                        @endif
                        <flux:button type="submit" variant="primary">{{ __('Submit Feedback') }}</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endvolt
</x-layouts.board>
