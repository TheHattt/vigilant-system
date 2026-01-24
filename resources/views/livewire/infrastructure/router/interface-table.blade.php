<div wire:poll.10s="refreshData">
    <flux:card>
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="lg">Network Interfaces</flux:heading>
                <flux:subheading>Manage physical ports and virtual links.</flux:subheading>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-[10px] font-bold uppercase text-zinc-500 ">Sync Status</p>
                    <p class="text-xs font-mono text-zinc-400">{{ $lastSync ?? '--:--:--' }}</p>
                </div>
                <flux:button wire:click="loadData" variant="ghost" icon="arrow-path" size="sm" />
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Interface</flux:table.column>
                <flux:table.column>Type</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>MAC Address</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($interfaces as $iface)
                    <flux:table.row :wire:key="'iface-'.$iface['name']">
                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium text-white">{{ $iface['name'] }}</span>
                                <span class="text-xs text-zinc-500">{{ $iface['comment'] ?? 'No comment' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="xs" variant="outline" class="capitalize">{{ $iface['type'] }}</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if(($iface['disabled'] ?? 'false') === 'true')
                                <flux:badge color="red" size="xs" inset>Disabled</flux:badge>
                            @elseif(($iface['running'] ?? 'false') === 'true')
                                <flux:badge color="emerald" size="xs" inset>Running</flux:badge>
                            @else
                                <flux:badge color="zinc" size="xs" inset>No Link</flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="font-mono text-xs text-zinc-500">
                            {{ $iface['mac-address'] ?? '---' }}
                        </flux:table.cell>

                        <flux:table.cell align="end">
                            <flux:button
                                wire:click="confirmToggle('{{ $iface['.id'] }}', '{{ $iface['name'] }}', '{{ $iface['disabled'] }}')"
                                variant="ghost"
                                size="xs"
                            >
                                {{ ($iface['disabled'] ?? 'false') === 'true' ? 'Enable' : 'Disable' }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="text-center py-12">
                            <flux:text color="zinc">No interfaces found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Interface Toggle Modal --}}
    <flux:modal name="interface-toggle-modal" class="min-w-[450px]">
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full {{ $isDisabling ? 'bg-red-500/10' : 'bg-emerald-500/10' }}">
                    <flux:icon name="{{ $isDisabling ? 'no-symbol' : 'check-circle' }}" class="{{ $isDisabling ? 'text-red-500' : 'text-emerald-500' }}" />
                </div>
                <div>
                    <flux:heading size="lg">{{ $isDisabling ? 'Disable' : 'Enable' }} {{ $selectedInterfaceName }}?</flux:heading>
                    <flux:subheading>
                        @if($isDisabling)
                            Disabling this port will disconnect all connected devices on this segment.
                        @else
                            Enabling this port will restore network traffic for this segment.
                        @endif
                    </flux:subheading>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="toggleInterface" color="{{ $isDisabling ? 'red' : 'emerald' }}">
                    Confirm {{ $isDisabling ? 'Disable' : 'Enable' }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
