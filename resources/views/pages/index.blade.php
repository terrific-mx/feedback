<?php

use Livewire\Volt\Component;

new class extends Component {

}; ?>

<x-layouts.board>
    @volt('pages.index')
        <div>
            <h1 class="text-2xl font-bold mb-4">Welcome to the Home Page</h1>
            <p>This is the main content area of the home page.</p>
        </div>
    @endvolt
</x-layouts.board>
