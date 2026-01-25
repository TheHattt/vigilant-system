{{-- @props(['router'])

<div class="group relative p-5 bg-zinc-900 border border-zinc-800 rounded-2xl hover:border-indigo-500/50 transition-all duration-300 shadow-sm">
    <div class="flex items-start justify-between mb-4">
        <div class="p-3 bg-zinc-800 rounded-xl group-hover:bg-indigo-500/10 transition-colors">
            <flux:icon.server variant="micro" class="{{ $router->is_online ? 'text-emerald-400' : 'text-red-400' }}" />
        </div>

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
</div> --}}
@props(['router'])

@php
    $isUnreachable = Cache::has("router.{$router->id}.unreachable");
    $statusColor = $router->is_online && !$isUnreachable ? 'emerald' : ($isUnreachable ? 'red' : 'zinc');
    $glowColor = $router->is_online && !$isUnreachable ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
@endphp

<div
    class="relative overflow-hidden rounded-2xl border border-white/5 bg-zinc-900/40 p-5 backdrop-blur-md transition-all duration-300 group-hover:border-white/10 group-hover:bg-zinc-800/50"
    style="box-shadow: 0 10px 30px -15px {{ $glowColor }};"
>
    {{-- Decorative Background Glow --}}
    <div class="absolute -right-4 -top-4 h-28 w-28 rounded-full blur-3xl transition-opacity duration-500 {{ $router->is_online ? 'bg-emerald-500/5' : 'bg-red-500/5' }} opacity-0 group-hover:opacity-100"></div>

    <div class="relative z-10">
        <div class="flex items-start justify-between mb-4">
            <div class="p-2 rounded-lg bg-white/10 border border-white/10">
                <flux:icon.server variant="micro" class="h-4 w-4 {{ $router->is_online ? 'text-emerald-400' : 'text-zinc-500' }}" />
            </div>

            <flux:badge size="xs" :color="$statusColor" class="!rounded-md border-none !bg-white/5 !text-[9px] font-black tracking-widest uppercase">
                {{ $router->is_online ? 'Active' : 'Offline' }}
            </flux:badge>
        </div>

        <div class="space-y-1">
            <h3 class="text-sm font-bold text-white truncate group-hover:text-indigo-300 transition-colors">
                {{ $router->name }}
            </h3>
            <p class="text-[10px] font-mono text-zinc-500 uppercase tracking-tighter">
                {{ $router->hostname }}
            </p>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3">
            <div class="rounded-lg bg-black/20 p-2 border border-white/[0.03]">
                <p class="text-[8px] font-bold text-zinc-500 uppercase tracking-wider mb-1">CPU Load</p>
                <div class="flex items-center gap-2">
                    <div class="h-1 flex-1 bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $router->cpu_load ?? 0 }}%"></div>
                    </div>
                    <span class="text-[9px] font-mono text-zinc-300">{{ $router->cpu_load ?? 0 }}%</span>
                </div>
            </div>

            <div class="rounded-lg bg-black/20 p-2 border border-white/[0.03]">
                <p class="text-[8px] font-bold text-zinc-500 uppercase tracking-wider mb-1">Uptime</p>
                <p class="text-[9px] font-mono text-zinc-300 truncate">
                    {{ $router->uptime_short ?? '0s' }}
                </p>
            </div>
        </div>

        {{-- Hover Action Indicator --}}
        <div class="mt-4 flex items-center justify-end opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
            <span class="text-[9px] font-black text-white uppercase tracking-[0.2em] flex items-center gap-1">
                Open Node <flux:icon.chevron-right variant="micro" class="h-2.5 w-2.5" />
            </span>
        </div>
    </div>
</div>
