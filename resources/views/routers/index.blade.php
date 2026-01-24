<x-layouts::app>
    <div x-data="{
            displayMode: 'grid',
            search: '',
            isLoading: true
         }"
         x-init="setTimeout(() => isLoading = false, 1000)"
         class="p-4 md:p-8 lg:p-12 bg-white dark:bg-zinc-800 min-h-screen rounded-lg">

        {{-- Header Section --}}
        <div class="mb-6 md:mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 md:pb-10 border-b border-neutral-200 dark:border-zinc-800">
            <div class="flex items-center gap-4 md:gap-5">
                <div class="relative">
                    <flux:avatar src="{{ auth()->user()->avatar_url }}" :initials="auth()->user()->initials()" size="lg" class="md:size-xl rounded-md border border-neutral-200 dark:border-zinc-700 shadow-sm" />
                    <div class="absolute -bottom-1 -right-1 h-3 w-3 bg-emerald-500 rounded-md border-2 border-white dark:border-zinc-900 animate-pulse"></div>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-neutral-900 dark:text-neutral-50">
                        {{ auth()->user()->name }}
                    </h1>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-[10px] md:text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-2 py-0.5 md:px-2.5 md:py-1 rounded-md border border-indigo-100 dark:border-indigo-500/20">System Administrator</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-6 md:gap-10 border-t sm:border-t-0 sm:border-l border-neutral-200 dark:border-zinc-800 pt-4 sm:pt-0 sm:pl-10">
                <div class="hidden md:block text-right">
                    <p class="text-[10px] font-bold text-neutral-400 mb-2 uppercase tracking-wider">Cluster Health</p>
                    <div class="flex items-end gap-1 h-4">
                        @for($i=0; $i<8; $i++)
                            <div class="w-1 rounded-sm bg-emerald-500 animate-[health-bounce_2s_infinite_ease-in-out]"
                                 style="animation-delay: {{ $i * 150 }}ms; height: 30%;"></div>
                        @endfor
                        <span class="ml-2 text-sm font-bold text-neutral-900 dark:text-neutral-100">Optimal</span>
                    </div>
                </div>
                <div class="text-left sm:text-right w-full sm:w-auto">
                    <p class="text-[10px] font-bold text-neutral-400 mb-1 uppercase tracking-wider">System Time</p>
                    <p class="text-base md:text-lg font-semibold text-neutral-900 dark:text-neutral-100 leading-none">
                        {{ now()->format('H:i') }} <span class="text-xs text-neutral-500 font-normal">UTC</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="flex flex-col space-y-4 md:flex-row md:space-y-0 justify-between items-center gap-4 mb-8">
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                <div class="relative w-full sm:w-64 md:w-80 group">
                    <flux:icon.magnifying-glass variant="micro" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-indigo-500 transition-colors" />
                    <input x-model="search" type="text" placeholder="Filter nodes..."
                        class="w-full bg-neutral-50 dark:bg-zinc-800/50 border border-neutral-200 dark:border-zinc-700 rounded-md py-2 pl-10 pr-4 text-sm font-medium text-neutral-800 dark:text-neutral-200 shadow-sm focus:border-indigo-500 outline-none transition-all">
                </div>

                <div class="hidden sm:flex p-1 bg-neutral-100 dark:bg-zinc-800 rounded-md border border-neutral-200 dark:border-zinc-700">
                    <button @click="displayMode = 'grid'" :class="displayMode === 'grid' ? 'bg-white dark:bg-zinc-700 shadow-sm text-neutral-900 dark:text-white' : 'text-neutral-500'" class="px-3 py-1 text-xs font-bold rounded-lg transition-all">Grid</button>
                    <button @click="displayMode = 'list'" :class="displayMode === 'list' ? 'bg-white dark:bg-zinc-700 shadow-sm text-neutral-900 dark:text-white' : 'text-neutral-500'" class="px-3 py-1 text-xs font-bold rounded-lg transition-all">List</button>
                </div>
            </div>

            <a href="{{ route('onboarding.mikrotik') }}" class="w-full sm:w-auto flex justify-center items-center px-5 py-2.5 bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold rounded-lg hover:opacity-90 transition-all shadow-md active:scale-95">
                <flux:icon.plus variant="micro" class="mr-2"/>
                Provision Node
            </a>
        </div>

        {{-- Stats --}}
        <x-infrastructure.stats :routers="$routers"/>

        {{-- Main Content --}}
        <div class="relative min-h-[400px] mt-8">
            {{-- Skeletons --}}
            <div x-show="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                @for($i=0; $i<8; $i++)
                    <div class="h-44 rounded-2xl bg-zinc-800/50 border border-zinc-800 animate-pulse"></div>
                @endfor
            </div>

            {{-- Router Cards --}}
            <div x-show="!isLoading"
                 x-cloak
                 x-transition:enter="transition ease-out duration-500"
                 :class="displayMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6' : 'flex flex-col gap-3'">

                @forelse($routers as $router)
                    <div x-show="'{{ strtolower($router->name) }}'.includes(search.toLowerCase())">
                        <x-infrastructure.node-card :router="$router" />
                    </div>
                @empty
                    <div class="col-span-full py-24 text-center border border-dashed border-zinc-800 rounded-2xl bg-zinc-900/50">
                        <flux:icon.server variant="micro" class="mx-auto text-zinc-700 mb-4 h-12 w-12" />
                        <h3 class="text-sm font-bold text-zinc-500">No Infrastructure Deployed</h3>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-10">
            <x-infrastructure.activity-log :routers="$routers" />
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes health-bounce {
            0%, 100% { height: 30%; opacity: 0.3; }
            50% { height: 100%; opacity: 1; }
        }
    </style>
</x-layouts::app>
