<x-layouts::app>
    <div class="min-h-screen bg-gradient-to-br from-neutral-900 via-zinc-900 to-black" x-data="{ activeTab: 'overview' }">
        {{-- Top Navigation Bar --}}
        <div class="sticky top-0 z-50 border-b border-white/5 bg-zinc-900/60 backdrop-blur-xl">
            <div class="px-4 py-3 md:px-6">
                <div class="mx-auto max-w-[1600px] flex items-center justify-between">
                    <div class="flex items-center gap-3 md:gap-6">
                        {{-- Aesthetic Back Button --}}
                        <a href="{{ route('router.index') }}"
                           wire:navigate.prefetch
                           class="group flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-200">
                            <flux:icon.arrow-left variant="micro" class="h-3 w-3 text-zinc-400 group-hover:-translate-x-0.5 group-hover:text-white transition-all" />
                            <span class="text-[11px] font-bold text-zinc-400 group-hover:text-white transition-colors uppercase tracking-widest">
                                <span class="hidden sm:inline">Infrastructure</span>
                                <span class="sm:hidden">Back</span>
                            </span>
                        </a>

                        <div class="h-6 w-px bg-zinc-800"></div>

                        <div class="flex items-center gap-3">
                            <div class="flex flex-col">
                                <h1 class="text-sm md:text-base font-bold text-white leading-none tracking-tight">
                                    {{ $router->name }}
                                </h1>
                                <span class="text-[10px] text-zinc-500 mt-1 font-mono uppercase tracking-tighter">
                                    {{ $router->model ?? 'MikroTik' }} • {{ $router->hostname }}
                                </span>
                            </div>

                            <flux:badge size="xs" :color="$router->is_online ? 'emerald' : 'red'" class="shadow-sm !rounded-md">
                                <span class="flex items-center gap-1.5 px-1">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $router->is_online ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 {{ $router->is_online ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    </span>
                                    <span class="text-[9px] font-black uppercase tracking-widest">{{ $router->is_online ? 'Live' : 'Offline' }}</span>
                                </span>
                            </flux:badge>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button icon="document-arrow-down" variant="ghost" size="sm" class="text-zinc-400 hover:text-white" wire:click="exportConfig">
                            <span class="hidden md:inline">Backup</span>
                        </flux:button>

                        <flux:button icon="cog-6-tooth" variant="filled" size="sm" class="!bg-zinc-100 !text-zinc-900 hover:!bg-white" x-on:click="$flux.modal('edit-router').show()">
                            <span class="hidden md:inline text-[11px] font-bold uppercase">Settings</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Container --}}
        <div class="mx-auto max-w-[1600px] p-4 md:p-6 lg:p-8 space-y-8">

            {{-- Dynamic Resource Cards (CPU, RAM, Disk, Uptime) --}}
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                {{-- POLL REMOVED --}}
                <livewire:infrastructure.router.resource-cards :router="$router" />
            </div>

            {{-- Custom Segmented Tabs --}}
            <div class="flex p-1 space-x-1 bg-black/40 backdrop-blur-md rounded-xl border border-white/5 max-w-fit shadow-2xl">
                <button @click="activeTab = 'overview'; $wire.dispatch('tab-changed', { tab: 'overview' })"
                        :class="activeTab === 'overview' ? 'bg-zinc-800 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                        class="flex items-center gap-2 px-6 py-2.5 text-[10px] font-black uppercase tracking-[0.2em] rounded-lg transition-all duration-300">
                    <flux:icon.squares-2x2 class="h-3.5 w-3.5" />
                    Overview
                </button>
                <button @click="activeTab = 'ppp'; $wire.dispatch('tab-changed', { tab: 'ppp' })"
                        :class="activeTab === 'ppp' ? 'bg-zinc-800 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                        class="flex items-center gap-2 px-6 py-2.5 text-[10px] font-black uppercase tracking-[0.2em] rounded-lg transition-all duration-300">
                    <flux:icon.shield-check class="h-3.5 w-3.5" />
                    PPP Secrets
                </button>
                <button @click="activeTab = 'logs'; $wire.dispatch('tab-changed', { tab: 'logs' })"
                        :class="activeTab === 'logs' ? 'bg-zinc-800 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                        class="flex items-center gap-2 px-6 py-2.5 text-[10px] font-black uppercase tracking-[0.2em] rounded-lg transition-all duration-300">
                    <flux:icon.list-bullet class="h-3.5 w-3.5" />
                    Logs
                </button>
            </div>

            {{-- Tab: Overview --}}
            <template x-if="activeTab === 'overview'">
                <div x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        {{-- Left Column: Terminal & Chart --}}
                        <div class="lg:col-span-8 space-y-6">
                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                {{-- SSH Terminal --}}
                                <div class="rounded-2xl bg-black border border-zinc-800 shadow-2xl overflow-hidden flex flex-col h-[450px]">
                                    <div class="bg-zinc-900/80 px-4 py-3 flex justify-between items-center border-b border-white/5">
                                        <div class="flex items-center gap-2">
                                            <div class="flex gap-1.5 mr-2">
                                                <div class="w-2.5 h-2.5 rounded-full bg-red-500/20 border border-red-500/50"></div>
                                                <div class="w-2.5 h-2.5 rounded-full bg-amber-500/20 border border-amber-500/50"></div>
                                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/20 border border-emerald-500/50"></div>
                                            </div>
                                            <h3 class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Terminal Shell</h3>
                                        </div>
                                        <flux:badge size="xs" color="zinc" variant="outline" class="font-mono text-[9px] opacity-50">SSH_V2</flux:badge>
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <livewire:infrastructure.terminal :router-id="$router->id" wire:lazy />
                                    </div>
                                </div>

                                {{-- Throughput Chart --}}
                                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/50 p-6 shadow-sm h-[450px]">
                                    <div class="mb-4">
                                        <h3 class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Real-time Traffic</h3>
                                    </div>
                                    <livewire:infrastructure.router.throughput-chart :router="$router" wire:lazy />
                                </div>
                            </div>

                            {{-- Interfaces Table --}}
                            <div class="rounded-2xl border border-zinc-800 bg-zinc-950/30 overflow-hidden shadow-2xl">
                                {{-- POLL REMOVED --}}
                                <livewire:infrastructure.router.interface-table :router="$router" />
                            </div>
                        </div>

                        {{-- Right Column: Side Info --}}
                        <div class="lg:col-span-4 space-y-6">
                            <div class="rounded-2xl border border-zinc-800 bg-zinc-950/50 p-6 backdrop-blur-sm">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-white mb-6 flex items-center gap-2">
                                    <flux:icon.information-circle class="h-4 w-4 text-indigo-400" />
                                    Hardware Identity
                                </h3>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/5">
                                        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-tighter">Model</span>
                                        <span class="text-xs font-bold text-zinc-200">{{ $router->model ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/5">
                                        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-tighter">OS Version</span>
                                        <span class="text-xs font-bold text-zinc-200">{{ $router->version ?? 'RouterOS' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-white/5 border border-white/5">
                                        <span class="text-[11px] font-medium text-zinc-500 uppercase tracking-tighter">Serial</span>
                                        <span class="text-xs font-mono text-zinc-400">{{ $router->serial_number ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Tab: PPP Secrets --}}
            <template x-if="activeTab === 'ppp'">
                <div x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8">
                    <livewire:infrastructure.router.ppp-secrets :router="$router" :key="'ppp-'.$router->id" />
                </div>
            </template>

            {{-- Tab: Logs --}}
            <template x-if="activeTab === 'logs'">
                <div x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8">
                    <div class="rounded-2xl overflow-hidden border border-zinc-800 bg-black shadow-2xl">
                        <livewire:infrastructure.router.system-logs :router="$router" />
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Edit Modal --}}
    <flux:modal name="edit-router" variant="side" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Router Configuration</flux:heading>
                <flux:subheading>Management for {{ $router->name }}</flux:subheading>
            </div>
            <livewire:infrastructure.router.settings-form :router="$router" />
        </div>
    </flux:modal>
</x-layouts::app>
