@props(['router'])

<div class="group relative p-5 bg-zinc-900 border border-zinc-800 rounded-2xl hover:border-indigo-500/50 transition-all duration-300 shadow-sm">
    <div class="flex items-start justify-between mb-4">
        <div class="p-3 bg-zinc-800 rounded-xl group-hover:bg-indigo-500/10 transition-colors">
            <flux:icon.server variant="micro" class="{{ $router->is_online ? 'text-emerald-400' : 'text-red-400' }}" />
        </div>

        {{-- Removed 'top-bottom' to fix array key error --}}
        <flux:badge :color="$router->is_online ? 'emerald' : 'red'" size="sm" class="capitalize">
            {{ $router->is_online ? 'Online' : 'Offline' }}
        </flux:badge>
    </div>

    <div class="mb-6">
        <h3 class="font-bold text-white group-hover:text-indigo-400 transition-colors truncate">
            {{ $router->name }}
        </h3>
        <p class="text-[10px] text-zinc-500 mt-1 font-mono uppercase tracking-wider">
            {{ $router->hardware_name }} • {{ $router->model }}
        </p>
    </div>

    <div class="pt-4 border-t border-zinc-800 flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-[9px] text-zinc-600 uppercase font-bold tracking-widest">Network Host</span>
            <span class="text-xs text-zinc-300 font-mono">{{ $router->hostname ?? $router->host }}</span>
        </div>

        <a href="{{ route('router.show', $router) }}"
            wire:navigate
            class="p-2 rounded-lg bg-zinc-800 text-zinc-400 hover:text-white hover:bg-zinc-700 transition-all">
            <flux:icon.chevron-right variant="micro" />
        </a>
    </div>
</div>
