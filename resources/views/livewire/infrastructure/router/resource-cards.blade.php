<div wire:poll.10s="refreshData">
    @if(!$hasData && !$isRebooting)
        <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @for($i = 0; $i < 4; $i++)
                <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-5 animate-pulse h-36"></div>
            @endfor
        </div>
    @else
        {{-- Resource Cards Grid --}}
        <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative">
            {{-- Overlay for Rebooting State --}}
            @if($isRebooting)
                <div class="absolute inset-0 z-10 bg-zinc-950/60 backdrop-blur-sm flex flex-col items-center justify-center rounded-xl border border-zinc-800">
                    <flux:heading size="lg">Router Rebooting</flux:heading>
                    <flux:subheading>Waiting for hardware heartbeat...</flux:subheading>
                </div>
            @endif

            <x-router-stat-card label="Uptime" :value="$isRebooting ? 'Offline' : $uptime" icon="clock" color="{{ $isRebooting ? 'zinc' : 'emerald' }}" :progress="$isRebooting ? 0 : 100" />

            <x-router-stat-card
                label="CPU Load"
                :value="$isRebooting ? '--' : $cpuLoad . '%'"
                icon="cpu-chip"
                :color="$cpuLoad > 80 ? 'red' : 'indigo'"
                :progress="$isRebooting ? 0 : $cpuLoad"
            />

            <x-router-stat-card
                label="Memory"
                :value="$isRebooting ? '--' : $memory['percentage'] . '%'"
                icon="server-stack"
                :color="$memory['percentage'] > 90 ? 'red' : 'amber'"
                :progress="$isRebooting ? 0 : $memory['percentage']"
                :subtext="$isRebooting ? '' : $memory['used'] . ' / ' . $memory['total'] . ' MB'"
            />

            <x-router-stat-card
                label="Temp"
                :value="$isRebooting ? '--' : ($temperature ? $temperature . '°C' : 'N/A')"
                icon="fire"
                color="emerald"
                :progress="$isRebooting ? 0 : ($temperature ?? 0)"
            />
        </div>

        {{-- Footer Action Bar --}}
        <div class="flex items-center justify-between px-4 py-4 border-t border-zinc-800">
            <div class="flex items-center gap-2 text-xs text-zinc-500">
                <div class="h-2 w-2 rounded-full {{ $isRebooting ? 'bg-zinc-600' : ($isHealthy ? 'bg-emerald-500' : 'bg-red-500') }}"></div>
                <span>{{ $isRebooting ? 'Awaiting Connection' : 'Sync: ' . now()->format('H:i:s') }}</span>
            </div>

            <div class="flex items-center gap-2">
                <flux:modal.trigger name="reboot-confirmation">
                    <flux:button variant="ghost" size="xs" icon="power" class="text-zinc-500 hover:text-red-500">Reboot Hardware</flux:button>
                </flux:modal.trigger>

                <flux:button wire:click="refreshData" variant="ghost" size="xs" icon="arrow-path">Refresh</flux:button>
            </div>
        </div>

        {{-- Professional Confirmation Modal --}}
        <flux:modal name="reboot-confirmation" class="min-w-[400px]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Confirm System Reboot</flux:heading>
                    <flux:subheading>This action will terminate all active PPPoE and Hotspot sessions. Internet access will be unavailable for 2-3 minutes.</flux:subheading>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>
                    <flux:button wire:click="triggerReboot" variant="danger">Confirm Reboot</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
