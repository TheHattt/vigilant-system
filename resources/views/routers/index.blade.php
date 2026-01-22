<x-layouts::app>
    <div class="min-h-screen bg-gray-50 dark:bg-[#0f1116] text-gray-900 dark:text-gray-100">
        <header class="bg-white dark:bg-[#161b22] border-b border-gray-200 dark:border-gray-800 sticky top-0 z-30">
            <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                        <h2 class="font-black text-xl tracking-tight uppercase">Core Infrastructure</h2>
                    </div>
                    <p class="text-[11px] font-mono text-gray-500 dark:text-gray-400">Environment: Production // Nodes: {{ $routers->count() }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden lg:flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-1 border dark:border-gray-700">
                        <button class="px-3 py-1.5 text-xs font-bold rounded-md bg-white dark:bg-gray-700 shadow-sm">Grid</button>
                        <button class="px-3 py-1.5 text-xs font-bold text-gray-500">List</button>
                    </div>
                    <a href="{{ route('onboarding.mikrotik') }}" class="flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition-all shadow-lg shadow-indigo-500/20 uppercase tracking-widest">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Provision Node
                    </a>
                </div>
            </div>
        </header>

        <main class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                @php
                    $stats = [
                        ['label' => 'Uptime Avg', 'val' => '99.98%', 'color' => 'text-green-500'],
                        ['label' => 'Total Bandwidth', 'val' => '4.2 Gbps', 'color' => 'text-blue-500'],
                        ['label' => 'Active Leases', 'val' => '1,240', 'color' => 'text-indigo-500'],
                        ['label' => 'Critical Alerts', 'val' => '0', 'color' => 'text-gray-500'],
                    ];
                @endphp
                @foreach($stats as $stat)
                    <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-gray-800 p-4 rounded-xl">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $stat['label'] }}</span>
                        <p class="text-2xl font-mono font-bold {{ $stat['color'] }}">{{ $stat['val'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($routers as $router)
                <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden hover:border-indigo-500/50 transition-all duration-300 group shadow-sm">
                    <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex justify-between items-start bg-gray-50/50 dark:bg-gray-800/20">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="h-10 w-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white shadow-inner">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <div class="absolute -bottom-1 -right-1 h-3 w-3 rounded-full border-2 border-white dark:border-[#161b22] {{ $router->is_online ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            </div>
                            <div>
                                <h3 class="text-sm font-black dark:text-white uppercase tracking-tight">{{ $router->name }}</h3>
                                <p class="text-[10px] font-mono text-gray-500 uppercase">{{ $router->model }} • {{ $router->hostname }}</p>
                            </div>
                        </div>
                        <button class="text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg></button>
                    </div>

                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">CPU Load</span>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-green-500 h-full w-[12%]"></div>
                                </div>
                                <p class="text-[10px] font-mono text-gray-600 dark:text-gray-300">12% / 40°C</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Memory usage</span>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-indigo-500 h-full w-[45%]"></div>
                                </div>
                                <p class="text-[10px] font-mono text-gray-600 dark:text-gray-300">114MB / 256MB</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-6">
                            <div class="px-2 py-1 bg-gray-100 dark:bg-gray-800 border dark:border-gray-700 rounded text-[10px] font-bold text-gray-500 dark:text-gray-400">
                                RADIUS: <span class="text-green-500">3799</span>
                            </div>
                            <div class="px-2 py-1 bg-gray-100 dark:bg-gray-800 border dark:border-gray-700 rounded text-[10px] font-bold text-gray-500 dark:text-gray-400">
                                API: <span class="text-green-500">{{ $router->api_port }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex -space-x-1">
                                <span class="h-6 w-6 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-[8px] flex items-center justify-center font-bold text-indigo-500">P1</span>
                                <span class="h-6 w-6 rounded-full bg-gray-500/10 border border-gray-500/20 text-[8px] flex items-center justify-center font-bold text-gray-500">+</span>
                            </div>
                            <a href="{{ route('router.show', $router->id) }}" class="inline-flex items-center text-[11px] font-black text-indigo-500 hover:text-indigo-400 uppercase tracking-widest transition-colors">
                                Open Console
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </main>
    </div>
</x-layouts::app>
