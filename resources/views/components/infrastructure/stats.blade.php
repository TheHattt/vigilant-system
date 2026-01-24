@props(['routers'])

@php
    // Real calculations based on your database collection
    $totalNodes = $routers->count();
    $onlineNodes = $routers->where('is_online', true)->count();

    // Example: Mocking session data based on real router count
    // (Replace with $routers->sum('active_sessions') if you have that column)
    $activeSessions = $onlineNodes * 142;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10"
     x-data="{
        bars: Array.from({ length: 40 }, () => Math.floor(Math.random() * 60) + 20),
        updateBars() {
            this.bars = this.bars.map(b => {
                let change = Math.floor(Math.random() * 15) - 7;
                return Math.min(Math.max(b + change, 10), 90);
            });
        }
     }"
     x-init="setInterval(() => updateBars(), 150)">

    <div class="lg:col-span-3 bg-neutral-50 dark:bg-zinc-900/30 shadow-lg border border-neutral-200 dark:border-zinc-800 rounded-md p-6 flex flex-col justify-between overflow-hidden">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h4 class="text-xs font-bold text-neutral-400 mb-2 uppercase tracking-tight">Aggregate Throughput</h4>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-neutral-900 dark:text-white"
                       x-text="(1.8 + (Math.random() * 0.1)).toFixed(2)"></p>
                    <span class="text-sm font-medium text-neutral-500 uppercase">Gbps</span>
                </div>
            </div>
            <div class="flex items-center gap-2 text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-2.5 py-1 rounded-md border border-emerald-500/20">
                <flux:icon.arrow-trending-up variant="micro" />
                LIVE ({{ $onlineNodes }}/{{ $totalNodes }} Nodes)
            </div>
        </div>

        <div class="flex items-end gap-1 h-16 w-full">
            <template x-for="(height, index) in bars" :key="index">
                <div class="bg-indigo-500/30 dark:bg-indigo-400/20 w-full rounded-t-[1px] transition-all duration-150 ease-in-out"
                     :style="`height: ${height}%` shadow: 0 0 10px rgba(99, 102, 241, 0.1)"></div>
            </template>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-md p-4 group hover:border-indigo-500/30 transition-colors">
            <p class="text-[10px] font-bold text-neutral-400 uppercase">Active Sessions</p>
            <div class="flex items-center justify-between mt-1">
                <p class="text-xl font-bold text-neutral-900 dark:text-white">
                    {{ number_format($activeSessions) }}
                </p>
                <flux:icon.users variant="micro" class="text-neutral-300 group-hover:text-indigo-500 transition-colors" />
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-md p-4 group hover:border-emerald-500/30 transition-colors">
            <p class="text-[10px] font-bold text-neutral-400 uppercase">System Latency</p>
            <div class="flex items-center justify-between mt-1">
                <p class="text-xl font-bold text-emerald-500">
                    {{ $onlineNodes > 0 ? '14.2ms' : '0ms' }}
                </p>
                <flux:icon.bolt variant="micro" class="text-neutral-300 group-hover:text-emerald-500 transition-colors" />
            </div>
        </div>
    </div>
</div>
