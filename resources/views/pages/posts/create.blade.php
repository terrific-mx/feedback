<?php

use Livewire\Volt\Component;

new class extends Component {

}; ?>

<x-layouts.app>
    @volt('pages.posts.create')
        <form class="space-y-6">
            <flux:input label="Title" />
            <flux:textarea label="Description" />
            <flux:button variant="primary">Create</flux:button>
        </form>
    @endvolt
</x-layouts.app>
