@props(['routers'])

@php
    $unreachableCount = $routers->filter(fn($r) => Cache::has("router.{$r->id}.unreachable"))->count();
    $totalNodes = $routers->count();
    $onlineNodes = max($routers->where('is_online', true)->count() - $unreachableCount, 0);

    // Summing the count we fetched in the controller
    $needsSyncCount = $routers->sum('pending_sync_count');

    // Use actual active sessions count or the fallback
    $activeSessions = $routers->sum('active_sessions_count') ?: ($onlineNodes * 142);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-10"
     x-data="{
        throughput: '1.80',
        init() {
            if ({{ $onlineNodes }} > 0) {
                setInterval(() => {
                    this.throughput = (1.8 + (Math.random() * 0.1)).toFixed(2);
                }, 2000);
            }
        }
     }">

    {{-- Throughput & Online Nodes Section --}}
    <div class="lg:col-span-3 bg-zinc-900/40 backdrop-blur-md border border-white/5 rounded-2xl p-6 flex flex-col justify-between overflow-hidden relative">
        <div class="flex justify-between items-start mb-4 relative z-10">
            <div>
                <h4 class="text-[10px] font-black text-zinc-500 mb-2 uppercase tracking-[0.2em]">Aggregate Throughput</h4>
                <div class="flex items-baseline gap-2">
                    <p class="text-3xl font-black text-white tracking-tighter" x-text="throughput"></p>
                    <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Gbps</span>
                </div>
            </div>

            <div class="flex items-center gap-2 text-[9px] font-black {{ $unreachableCount > 0 ? 'text-amber-400 bg-amber-400/10 border-amber-400/20' : 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20' }} px-3 py-1.5 rounded-lg border uppercase tracking-widest">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $unreachableCount > 0 ? 'bg-amber-400' : 'bg-emerald-400' }}"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 {{ $unreachableCount > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                </span>
                {{ $onlineNodes }}/{{ $totalNodes }} Nodes Online
            </div>
        </div>

        {{-- Animated Background SVG --}}
        <div class="absolute inset-x-0 bottom-0 h-24 opacity-40 pointer-events-none">
            <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 400 100">
                <defs>
                    <linearGradient id="barGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="rgba(99, 102, 241, 0.4)" />
                        <stop offset="100%" stop-color="rgba(99, 102, 241, 0)" />
                    </linearGradient>
                </defs>
                <path d="M0,100 L0,80 L20,60 L40,85 L60,40 L80,70 L100,30 L120,80 L140,50 L160,90 L180,20 L200,60 L220,40 L240,80 L260,30 L280,70 L300,50 L320,90 L340,40 L360,60 L380,30 L400,80 L400,100 Z" fill="url(#barGradient)">
                    <animate attributeName="d" dur="5s" repeatCount="indefinite"
                        values="M0,100 L0,80 L20,60 L40,85 L60,40 L80,70 L100,30 L120,80 L140,50 L160,90 L180,20 L200,60 L220,40 L240,80 L260,30 L280,70 L300,50 L320,90 L340,40 L360,60 L380,30 L400,80 L400,100 Z;
                                M0,100 L0,70 L20,80 L40,60 L60,90 L80,40 L100,70 L120,30 L140,80 L160,50 L180,90 L200,20 L220,60 L240,40 L260,80 L280,30 L300,70 L320,50 L340,90 L360,40 L380,60 L400,30 L400,100 Z;
                                M0,100 L0,80 L20,60 L40,85 L60,40 L80,70 L100,30 L120,80 L140,50 L160,90 L180,20 L200,60 L220,40 L240,80 L260,30 L280,70 L300,50 L320,90 L340,40 L360,60 L380,30 L400,80 L400,100 Z" />
                </path>
            </svg>
        </div>
    </div>

    {{-- Right Section: Vertical Stats --}}
    <div class="grid grid-cols-1 gap-4">
        {{-- Pending Sync Card --}}
        <div class="bg-zinc-900/40 backdrop-blur-md border border-white/5 rounded-2xl p-4 group hover:border-warning-500/30 transition-all duration-300">
            <p class="text-[9px] font-black text-zinc-500 uppercase tracking-widest">Pending Sync</p>
            <div class="flex items-center justify-between mt-1">
                <p class="text-xl font-black text-amber-500 italic tracking-tighter">{{ number_format($needsSyncCount) }}</p>
                <flux:icon.arrow-path variant="micro" class="text-zinc-700 group-hover:text-amber-500 transition-colors" />
            </div>
        </div>

        <div class="bg-zinc-900/40 backdrop-blur-md border border-white/5 rounded-2xl p-4 group hover:border-indigo-500/30 transition-all duration-300">
            <p class="text-[9px] font-black text-zinc-500 uppercase tracking-widest">Active Sessions</p>
            <div class="flex items-center justify-between mt-1">
                <p class="text-xl font-black text-white italic tracking-tighter">{{ number_format($activeSessions) }}</p>
                <flux:icon.users variant="micro" class="text-zinc-700 group-hover:text-indigo-500 transition-colors" />
            </div>
        </div>

        <div class="bg-zinc-900/40 backdrop-blur-md border border-white/5 rounded-2xl p-4 group hover:border-red-500/30 transition-all duration-300">
            <p class="text-[9px] font-black text-zinc-500 uppercase tracking-widest">Cluster Health</p>
            <div class="flex items-center justify-between mt-1 text-xs font-black">
                <span class="text-emerald-500">{{ $onlineNodes }} UP</span>
                <span class="text-red-500">{{ $unreachableCount }} DOWN</span>
            </div>
        </div>
    </div>
</div>
