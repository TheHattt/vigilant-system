<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">

        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle
                icon="sidebar"
                variant="ghost"
                class="mr-2 text-zinc-800 dark:text-white"
                inset="left"
            />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden ml-4">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>

                @php $offlineCount = \App\Models\Router::where('is_online', false)->count(); @endphp

                <flux:navbar.item icon="server" :href="route('router.index')" :current="request()->routeIs('router.index')" wire:navigate>
                    {{ __('Routers') }}
                    @if($offlineCount > 0)
                        <x-slot name="badge">
                            <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">
                                {{ $offlineCount }}
                            </span>
                        </x-slot>
                    @endif
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <x-desktop-user-menu />
        </flux:header>

        <flux:main container>
            <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:sidebar.nav>
                    <flux:sidebar.group :heading="__('Platform')">
                        <flux:sidebar.item icon="home" :href="route('dashboard')" wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="server" :href="route('router.index')" wire:navigate>
                            {{ __('Routers') }}
                            @if($offlineCount > 0)
                                <x-slot name="badge">{{ $offlineCount }}</x-slot>
                            @endif
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                </flux:sidebar.nav>

                <flux:spacer />

                <flux:sidebar.nav>
                    <flux:sidebar.item icon="book-open-text" href="#" target="_blank">
                        {{ __('Docs') }}
                    </flux:sidebar.item>
                </flux:sidebar.nav>
            </flux:sidebar>

            <div class="flex-1 lg:p-10">
                {{ $slot }}
            </div>
        </flux:main>

        @fluxScripts
    </body>
</html>
