<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 flex items-center">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:brand href="/" :name="config('app.name')" class="max-lg:hidden">
                <div class="flex aspect-square items-center justify-center rounded-md bg-accent text-accent-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic-vocal">
                        <path d="m11 7.601-5.994 8.19a1 1 0 0 0 .1 1.298l.817.818a1 1 0 0 0 1.314.087L15.09 12"/>
                        <path d="M16.5 21.174C15.5 20.5 14.372 20 13 20c-2.058 0-3.928 2.356-6 2-2.072-.356-2.775-3.369-1.5-4.5"/>
                        <circle cx="16" cy="7" r="5"/>
                    </svg>
                </div>
            </flux:brand>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item href="/">
                    {{ __('Home') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <!-- Desktop User Menu -->
            @guest
            <flux:button :href="route('login')" variant="ghost" size="sm" class="hidden lg:inline-flex">
                {{ __('Sign In') }}
            </flux:button>
            @else
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :avatar="auth()->user()->avatar ?: null"
                    :initials="auth()->user()->initials()"
                    class="cursor-pointer"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-sm">
                                    @if (auth()->user()->avatar)
                                        <img src="{{ auth()->user()->avatar }}" />
                                    @else
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-sm bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    @endif
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/boards" wire:navigate>{{ __('Boards') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            @endguest
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar stashable sticky class="lg:hidden border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <flux:brand :name="config('app.name')" href="/" class="px-2">
                <div class="flex aspect-square items-center justify-center rounded-md bg-accent text-accent-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mic-vocal">
                        <path d="m11 7.601-5.994 8.19a1 1 0 0 0 .1 1.298l.817.818a1 1 0 0 0 1.314.087L15.09 12"/>
                        <path d="M16.5 21.174C15.5 20.5 14.372 20 13 20c-2.058 0-3.928 2.356-6 2-2.072-.356-2.775-3.369-1.5-4.5"/>
                        <circle cx="16" cy="7" r="5"/>
                    </svg>
                </div>
            </flux:brand>

            <flux:navlist variant="outline">
                <flux:navlist.group>
                    <flux:navlist.item href="/">
                        {{ __('Home') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
