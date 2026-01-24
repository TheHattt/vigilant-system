<x-layouts::app>
    <div class="min-h-screen bg-gradient-to-br from-neutral-700 via-zinc-900 to-black/10" x-data="{ activeTab: 'overview' }">
        {{-- Top Navigation Bar --}}
        <div class="sticky top-0 z-50 border-b border-white/5 bg-zinc-900/60 backdrop-blur-xl">
            <div class="px-4 py-3 md:px-6">
                <div class="mx-auto max-w-[1600px] flex items-center justify-between">
                    <div class="flex items-center gap-3 md:gap-6">
                        {{-- Aesthetic Back Button --}}
                        <a href="{{ route('router.index') }}"
                           wire:navigate
                           class="group flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-200"
                        >
                            <flux:icon.arrow-left variant="micro" class="h-3 w-3 text-zinc-400 group-hover:-translate-x-0.5 group-hover:text-white transition-all" />
                            <span class="text-[11px] font-bold text-zinc-400 group-hover:text-white transition-colors">
                                <span class="hidden sm:inline">Infrastructure</span>
                                <span class="sm:hidden">Back</span>
                            </span>
                        </a>

                        <div class="h-6 w-px bg-zinc-800"></div>

                        <div class="flex items-center gap-3">
                            <div class="flex flex-col">
                                <h1 class="text-sm md:text-base font-bold text-white leading-none ">
                                    {{ $router->name }}
                                </h1>
                                <span class="text-[10px] text-zinc-500 mt-1 hidden xs:block">ID: {{ str_pad($router->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </div>

                            <flux:badge size="xs" :color="$router->is_online ? 'emerald' : 'red'" class="shadow-sm">
                                <span class="flex items-center gap-1.5">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $router->is_online ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 {{ $router->is_online ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    </span>
                                    <span class="hidden sm:inline text-[10px] font-bold ">{{ $router->is_online ? 'Live' : 'Offline' }}</span>
                                </span>
                            </flux:badge>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button
                            href="{{ route('router.export', $router) }}"
                            icon="document-arrow-down"
                            variant="ghost"
                            size="sm"
                            class="text-zinc-400 hover:text-white hover:bg-white/5"
                        >
                            <span class="hidden md:inline">Export</span>
                        </flux:button>

                        <flux:button
                            icon="cog-6-tooth"
                            variant="filled"
                            size="sm"
                            class="!bg-zinc-100 !text-zinc-900 hover:!bg-white"
                            x-on:click="$flux.modal('edit-router').show()"
                        >
                            <span class="hidden md:inline">Settings</span>
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Container --}}
        <div class="mx-auto max-w-[1600px] p-4 md:p-6 lg:p-8 space-y-6">

            {{-- Pass router to resource cards --}}
            <livewire:infrastructure.router.resource-cards :router="$router" />

            {{-- Custom Segmented Tabs --}}
            <div class="flex p-1 space-x-1 bg-zinc-900/60 rounded-xl border border-white/5 max-w-fit shadow-inner">
                <button
                    @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'bg-zinc-800 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                    class="flex items-center gap-2 px-4 py-2 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-all duration-200"
                >
                    <flux:icon.squares-2x2 class="h-4 w-4" />
                    Overview
                </button>
                <button
                    @click="activeTab = 'ppp'"
                    :class="activeTab === 'ppp' ? 'bg-zinc-800 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                    class="flex items-center gap-2 px-4 py-2 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-all duration-200"
                >
                    <flux:icon.shield-check class="h-4 w-4" />
                    PPP Secrets
                </button>
                <button
                    @click="activeTab = 'logs'"
                    :class="activeTab === 'logs' ? 'bg-zinc-800 text-white shadow-lg' : 'text-zinc-500 hover:text-zinc-300'"
                    class="flex items-center gap-2 px-4 py-2 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-all duration-200"
                >
                    <flux:icon.list-bullet class="h-4 w-4" />
                    Logs
                </button>
            </div>

            {{-- Tab: Overview --}}
            <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8">
                    <div class="lg:col-span-8 space-y-6 md:space-y-8">
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 md:gap-8">
                            {{-- Terminal --}}
                            <div class="rounded-xl border-none bg-black overflow-hidden flex flex-col h-[400px] md:h-[480px] shadow-2xl">
                                <div class="border-b border-zinc-900 bg-zinc-800 px-4 py-3 flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                        <h3 class="text-xs font-semibold text-white ">SSH Terminal</h3>
                                    </div>
                                    <flux:badge size="xs" color="zinc" variant="outline" class="text-[10px]">Port 22</flux:badge>
                                </div>
                                <div class="flex-1 p-2 bg-black text-green-500 text-[10px] md:text-xs">
                                    <livewire:infrastructure.terminal :router-id="$router->id" />
                                </div>
                            </div>

                            {{-- Throughput Chart --}}
                            <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-4 md:p-6 flex flex-col h-[400px] md:h-[480px] shadow-sm">
                                <livewire:infrastructure.router.throughput-chart :router="$router" />
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-zinc-800 bg-zinc-900/40 shadow-sm">
                            <livewire:infrastructure.router.interface-table :router="$router" />
                        </div>
                    </div>

                    <div class="lg:col-span-4 space-y-6 md:space-y-8">
                        <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-6 shadow-sm">
                            <h3 class="text-xs font-semibold text-white mb-6 flex items-center gap-2 ">
                                <flux:icon.information-circle class="h-4 w-4 text-zinc-500" />
                                Hardware Profile
                            </h3>
                            <div class="space-y-1">
                                <div class="flex items-center justify-between py-3 border-b border-zinc-800/50">
                                    <span class="text-xs text-zinc-500">Model</span>
                                    <span class="text-xs font-medium text-zinc-200">{{ $router->model }}</span>
                                </div>
                                <div class="flex items-center justify-between py-3">
                                    <span class="text-xs text-zinc-500">IPv4 Address</span>
                                    <span class="text-xs font-medium text-zinc-200 ">{{ $router->hostname }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: PPP Secrets --}}
            <div x-show="activeTab === 'ppp'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                {{-- Updated to use tag-based syntax for better parameter passing --}}
                <livewire:infrastructure.router.ppp-secrets :router="$router" :key="'ppp-'.$router->id" />
            </div>

            {{-- Tab: Logs --}}
            <div x-show="activeTab === 'logs'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                <div class="rounded-xl overflow-hidden border border-zinc-800 bg-neutral-950/100">
                     <livewire:infrastructure.router.system-logs :router="$router" />
                </div>
            </div>
        </div>
    </div>

    <flux:modal name="edit-router" variant="side" class="w-full max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Router Configuration</flux:heading>
                <flux:subheading>Update management details for {{ $router->name }}</flux:subheading>
            </div>
            <livewire:infrastructure.router.settings-form :router="$router" />
        </div>
    </flux:modal>
</x-layouts::app>
