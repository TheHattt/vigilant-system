<x-layouts::app>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">
                    {{ __('Network Infrastructure') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Enterprise Node Management & Provisioning</p>
            </div>
            <div class="flex gap-3">
                <button class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
                <a href="{{ route('onboarding.mikrotik') }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Provision Node
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <span class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">Total Nodes</span>
                    <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $routers->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <span class="text-green-500 text-xs font-bold uppercase tracking-wider">Operational</span>
                    <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $routers->where('is_online', true)->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <span class="text-red-500 text-xs font-bold uppercase tracking-wider">Critical/Offline</span>
                    <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $routers->where('is_online', false)->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm text-indigo-600">
                    <span class="text-gray-500 text-xs font-bold uppercase tracking-wider">Avg Latency</span>
                    <p class="text-3xl font-black mt-1">14ms</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($routers as $router)
                <div class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full {{ $router->is_online ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl group-hover:bg-indigo-50 dark:group-hover:bg-indigo-900/20 transition-colors">
                                    <svg class="w-8 h-8 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $router->name }}</h3>
                                    <p class="text-xs font-mono text-gray-500 dark:text-gray-400 uppercase">{{ $router->model }} • v7.12.1</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black tracking-tighter uppercase {{ $router->is_online ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                    {{ $router->is_online ? 'Active' : 'Down' }}
                                </span>
                                <p class="mt-2 text-xs font-semibold text-gray-400">{{ $router->hostname }}</p>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                                <span class="text-[10px] text-gray-400 font-bold uppercase">Management Interface</span>
                                <p class="text-sm font-semibold dark:text-gray-200">Port {{ $router->api_port }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                                <span class="text-[10px] text-gray-400 font-bold uppercase">RADIUS CoA</span>
                                <p class="text-sm font-semibold dark:text-gray-200">Port 3799</p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-between gap-4">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-800 bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-[10px] font-bold text-indigo-600 dark:text-indigo-400">P1</div>
                                <div class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-800 bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-[10px] font-bold text-gray-400">+</div>
                            </div>
                            <a href="{{ route('router.show', $router->id) }}" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                Terminal & Config →
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts::app>
