<div>
    <flux:card class="!bg-zinc-900/50 border-zinc-800">
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="lg">Router Activity</flux:heading>
                <flux:subheading>Combined hardware events and administrative changes.</flux:subheading>
            </div>
            <flux:button wire:click="refreshLogs" variant="ghost" icon="arrow-path" size="xs" class="text-zinc-400 hover:text-white">
                Sync Feed
            </flux:button>
        </div>

        <div class="space-y-4 md:h-[600px] overflow-y-auto pr-2 custom-scrollbar">
            @forelse($logs as $log)
                @php
                    $isDb = $log['is_db'] ?? false;
                    $topics = $log['topics'] ?? '';
                    $isError = str_contains($topics, 'error') || str_contains($topics, 'critical');
                    $isWarning = str_contains($topics, 'warning');
                @endphp

                <div class="group relative flex items-start gap-4 p-3 rounded-xl transition-all {{ $isDb ? 'bg-indigo-500/5 border border-indigo-500/10' : 'hover:bg-white/[0.02]' }}">
                    <div class="mt-1.5 relative">
                        <div class="h-2 w-2 rounded-full {{ $isDb ? 'bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]' : ($isError ? 'bg-red-500' : 'bg-zinc-700') }}"></div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-mono text-zinc-500 uppercase">{{ $log['time'] }}</span>

                            @if($isDb)
                                <span class="text-[9px] font-black bg-indigo-500/20 text-indigo-400 px-1.5 py-0.5 rounded uppercase tracking-widest">
                                    Admin: {{ $log['user'] }}
                                </span>
                            @else
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-tighter
                                    {{ $isError ? 'bg-red-500/10 text-red-500' : ($isWarning ? 'bg-amber-500/10 text-amber-500' : 'bg-zinc-800 text-zinc-500') }}">
                                    {{ $topics ?: 'SYSTEM' }}
                                </span>
                            @endif
                        </div>

                        <p class="text-sm leading-relaxed {{ $isDb ? 'text-zinc-200' : 'text-zinc-400' }}">
                            {{ $log['message'] }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="py-20 text-center">
                    {{-- Fixed the color prop here --}}
                    <flux:text variant="subtle">No activity recorded yet.</flux:text>
                </div>
            @endforelse
        </div>
    </flux:card>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 3px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
    </style>
</div>
