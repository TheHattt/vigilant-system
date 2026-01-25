<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    {{-- CPU Card --}}
    <div class="bg-zinc-900/50 border border-white/5 p-5 rounded-2xl">
        <div class="flex justify-between items-start mb-4">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">CPU Load</span>
            <flux:icon.cpu-chip class="h-4 w-4 text-zinc-600" />
        </div>
        <div class="flex items-end gap-2">
            <span class="text-3xl font-bold text-white">{{ $cpuLoad }}%</span>
            <div class="flex-1 h-1.5 bg-zinc-800 rounded-full mb-2 overflow-hidden">
                <div class="h-full {{ $cpuLoad > 80 ? 'bg-red-500' : 'bg-emerald-500' }}" style="width: {{ $cpuLoad }}%"></div>
            </div>
        </div>
    </div>

    {{-- RAM Card --}}
    <div class="bg-zinc-900/50 border border-white/5 p-5 rounded-2xl">
        <div class="flex justify-between items-start mb-4">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">Memory</span>
            <flux:icon.bolt class="h-4 w-4 text-zinc-600" />
        </div>
        <div class="flex items-end gap-2">
            <span class="text-3xl font-bold text-white">{{ $memory['percentage'] }}%</span>
            <span class="text-[10px] text-zinc-500 mb-1.5 font-mono">{{ $memory['used'] }}MB / {{ $memory['total'] }}MB</span>
        </div>
    </div>

    {{-- Uptime Card --}}
    <div class="bg-zinc-900/50 border border-white/5 p-5 rounded-2xl">
        <div class="flex justify-between items-start mb-4">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">System Uptime</span>
            <flux:icon.clock class="h-4 w-4 text-zinc-600" />
        </div>
        <span class="text-xl font-bold text-white font-mono uppercase tracking-tight">{{ $uptime }}</span>
    </div>

    {{-- Temperature/Health Card --}}
    <div class="bg-zinc-900/50 border border-white/5 p-5 rounded-2xl">
        <div class="flex justify-between items-start mb-4">
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">Status</span>
            @if($isHealthy)
                <flux:badge color="emerald" size="xs" variant="solid">Optimal</flux:badge>
            @else
                <flux:badge color="red" size="xs" variant="solid">Degraded</flux:badge>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <span class="text-2xl font-bold text-white">{{ $temperature ?? '--' }}°C</span>
            <span class="text-[10px] text-zinc-500 uppercase font-bold ">Thermal</span>
        </div>
    </div>
</div>
