<?php

use App\Models\Board;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class, 'can:create,App\Models\Board']);

new class extends Component {
    public string $name = '';
    public string $color = 'Zinc';

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|in:Zinc,Red,Orange,Amber,Yellow,Lime,Green,Emerald,Teal,Cyan,Sky,Blue,Indigo,Violet,Purple,Fuchsia,Pink,Rose',
        ]);

        Board::create([
            'name' => $this->name,
            'color' => $this->color,
        ]);

        $this->redirect('/boards');
    }
}; ?>

<x-layouts.board>
    @volt('pages.boards.create')
        <div>
            <div id="secondary-header" class="sm:border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                <div class="max-w-7xl px-6 sm:px-8 py-3 mx-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-2">
                    <div class="max-sm:hidden flex">
                        <flux:heading size="lg" class="text-lg">{{ __('New board') }}</flux:heading>
                    </div>
                </div>
            </div>
            <div class="min-h-4 sm:min-h-10"></div>
            <form id="form" wire:submit="create" class="mx-auto max-w-lg max-sm:px-2 space-y-6">
                <flux:input wire:model="name" :label="__('Name')" :placeholder="__('Features')" required />
                <flux:select wire:model="color" :label="__('Color')" required>
                    <flux:select.option value="Zinc">{{ __('Zinc') }}</flux:select.option>
                    <flux:select.option value="Red">{{ __('Red') }}</flux:select.option>
                    <flux:select.option value="Orange">{{ __('Orange') }}</flux:select.option>
                    <flux:select.option value="Amber">{{ __('Amber') }}</flux:select.option>
                    <flux:select.option value="Yellow">{{ __('Yellow') }}</flux:select.option>
                    <flux:select.option value="Lime">{{ __('Lime') }}</flux:select.option>
                    <flux:select.option value="Green">{{ __('Green') }}</flux:select.option>
                    <flux:select.option value="Emerald">{{ __('Emerald') }}</flux:select.option>
                    <flux:select.option value="Teal">{{ __('Teal') }}</flux:select.option>
                    <flux:select.option value="Cyan">{{ __('Cyan') }}</flux:select.option>
                    <flux:select.option value="Sky">{{ __('Sky') }}</flux:select.option>
                    <flux:select.option value="Blue">{{ __('Blue') }}</flux:select.option>
                    <flux:select.option value="Indigo">{{ __('Indigo') }}</flux:select.option>
                    <flux:select.option value="Violet">{{ __('Violet') }}</flux:select.option>
                    <flux:select.option value="Purple">{{ __('Purple') }}</flux:select.option>
                    <flux:select.option value="Fuchsia">{{ __('Fuchsia') }}</flux:select.option>
                    <flux:select.option value="Pink">{{ __('Pink') }}</flux:select.option>
                    <flux:select.option value="Rose">{{ __('Rose') }}</flux:select.option>
                </flux:select>
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                </div>
            </form>
        </div>
    @endvolt
</x-layouts.board>
