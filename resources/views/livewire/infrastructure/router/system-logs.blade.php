<div wire:poll.15s="refreshLogs" class="">
    <flux:card>
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="lg">System Events</flux:heading>
                <flux:subheading>Recent logs from the router hardware.</flux:subheading>
            </div>
            <flux:button wire:click="refreshLogs" variant="ghost" icon="arrow-path" size="xs">Sync Logs</flux:button>
        </div>

        <div class="space-y-1 md:h-[468px] overflow-y-auto pr-2 custom-scrollbar">
            @forelse($logs as $log)
                <div class="group flex items-start gap-4 p-2 rounded-lg hover:bg-zinc-800/50 transition-colors border-b border-zinc-800/30 last:border-0">
                    <span class="text-[10px]  text-zinc-500 pt-1 uppercase whitespace-nowrap">
                        {{ $log['time'] }}
                    </span>

                    <div class="flex flex-col gap-0.5">
                        <div class="flex items-center gap-2">
                            @php
                                $isError = str_contains($log['topics'], 'error') || str_contains($log['topics'], 'critical');
                                $isWarning = str_contains($log['topics'], 'warning');
                            @endphp

                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded
                                {{ $isError ? 'bg-red-500/10 text-red-500' : ($isWarning ? 'bg-amber-500/10 text-amber-500' : 'bg-zinc-800 text-zinc-400') }}">
                                {{ $log['topics'] }}
                            </span>
                        </div>
                        <p class="text-sm text-zinc-300 leading-relaxed font-sans">
                            {{ $log['message'] }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <flux:text color="zinc">No recent logs found.</flux:text>
                </div>
            @endforelse
        </div>
    </flux:card>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #3f3f46; }
    </style>
</div>
