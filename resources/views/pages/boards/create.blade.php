<?php

use App\Models\Board;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class, 'can:create,App\Models\Board']);

new class extends Component {
    public string $name = '';
    public string $color = 'zinc';

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|in:zinc,red,orange,amber,yellow,lime,green,emerald,teal,cyan,sky,blue,indigo,violet,purple,fuchsia,pink,rose',
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
                    <flux:select.option value="zinc">{{ __('Zinc') }}</flux:select.option>
                    <flux:select.option value="red">{{ __('Red') }}</flux:select.option>
                    <flux:select.option value="orange">{{ __('Orange') }}</flux:select.option>
                    <flux:select.option value="amber">{{ __('Amber') }}</flux:select.option>
                    <flux:select.option value="yellow">{{ __('Yellow') }}</flux:select.option>
                    <flux:select.option value="lime">{{ __('Lime') }}</flux:select.option>
                    <flux:select.option value="green">{{ __('Green') }}</flux:select.option>
                    <flux:select.option value="emerald">{{ __('Emerald') }}</flux:select.option>
                    <flux:select.option value="teal">{{ __('Teal') }}</flux:select.option>
                    <flux:select.option value="cyan">{{ __('Cyan') }}</flux:select.option>
                    <flux:select.option value="sky">{{ __('Sky') }}</flux:select.option>
                    <flux:select.option value="blue">{{ __('Blue') }}</flux:select.option>
                    <flux:select.option value="indigo">{{ __('Indigo') }}</flux:select.option>
                    <flux:select.option value="violet">{{ __('Violet') }}</flux:select.option>
                    <flux:select.option value="purple">{{ __('Purple') }}</flux:select.option>
                    <flux:select.option value="fuchsia">{{ __('Fuchsia') }}</flux:select.option>
                    <flux:select.option value="pink">{{ __('Pink') }}</flux:select.option>
                    <flux:select.option value="rose">{{ __('Rose') }}</flux:select.option>
                </flux:select>
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                </div>
            </form>
        </div>
    @endvolt
</x-layouts.board>
