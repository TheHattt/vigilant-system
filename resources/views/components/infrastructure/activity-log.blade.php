@props(['routers'])

@php
    $total = $routers->count();
    $online = $routers->where('is_online', true)->count();
    $offline = $total - $online;
    $uptime = $total > 0 ? round(($online / $total) * 100) : 0;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 p-5 rounded-md shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Total Nodes</span>
            <flux:icon.server variant="micro" class="text-neutral-400" />
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">{{ $total }}</span>
            <span class="text-xs text-neutral-500 font-medium whitespace-nowrap">Provisioned</span>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 p-5 rounded-md shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Active</span>
            <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-500">{{ $online }}</span>
            <span class="text-xs text-neutral-500 font-medium whitespace-nowrap">Online Now</span>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 p-5 rounded-md shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Down</span>
            <div class="h-2 w-2 rounded-full {{ $offline > 0 ? 'bg-red-500' : 'bg-neutral-300 dark:bg-zinc-700' }}"></div>
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-bold {{ $offline > 0 ? 'text-red-600' : 'text-neutral-900 dark:text-neutral-50' }}">
                {{ $offline }}
            </span>
            <span class="text-xs text-neutral-500 font-medium whitespace-nowrap">Unreachable</span>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 p-5 rounded-md shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">Health Score</span>
            <flux:icon.chart-bar variant="micro" class="text-neutral-400" />
        </div>
        <div class="flex items-baseline gap-2">
            <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $uptime }}%</span>
            <div class="w-full h-1 bg-neutral-100 dark:bg-zinc-800 rounded-full mt-2 hidden xs:block">
                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $uptime }}%"></div>
            </div>
        </div>
    </div>
</div>
