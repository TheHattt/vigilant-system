<div>
    <div class="flex flex-col h-[550px] overflow-hidden rounded-xl  bg-zinc-950 shadow-2xl">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-zinc-800 bg-zinc-900/50 px-4 py-3">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-2.5 py-1 rounded-md border {{ $connectionStatus === 'stable' ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-red-500/5 border-red-500/20' }}">
                    <span class="relative flex h-1.5 w-1.5">
                        @if($connectionStatus === 'stable')
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                        @else
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500"></span>
                        @endif
                    </span>
                    <span class="text-[10px] font-bold  {{ $connectionStatus === 'stable' ? 'text-emerald-500' : 'text-red-500' }}">
                        {{ $connectionStatus === 'stable' ? 'Connected' : 'Offline' }}
                    </span>
                </div>

                <flux:dropdown>
                    <flux:button variant="ghost" size="sm" icon="book-open" class="text-zinc-400 hover:text-white">Commands</flux:button>
                    <flux:menu class="bg-zinc-900 border-zinc-800">
                        @foreach($suggestions as $category => $cmds)
                            <flux:menu.group heading="{{ $category }}">
                                @foreach($cmds as $label => $val)
                                    <flux:menu.item wire:click="selectCommand('{{ $val }}')">{{ $label }}</flux:menu.item>
                                @endforeach
                            </flux:menu.group>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            </div>

            <button wire:click="clearHistory" class="text-zinc-600 hover:text-red-400 transition-colors">
                <flux:icon.trash variant="micro" />
            </button>
        </div>

        {{-- Output --}}
        <div class="flex-1 overflow-y-auto p-5  text-[13px] custom-scrollbar bg-black/20"
             x-init="$el.scrollTop = $el.scrollHeight"
             x-on:terminal-output-updated.window="$el.scrollTop = $el.scrollHeight">
            <div class="space-y-1.5">
                @foreach($history as $entry)
                    <div class="flex gap-3">
                        @if($entry['type'] === 'system')
                            <span class="text-indigo-400 opacity-50 select-none">[{{ now()->format('H:i') }}]</span>
                            <span class="italic text-zinc-600">{{ $entry['line'] }}</span>
                        @elseif($entry['type'] === 'input')
                            <span class="font-bold text-emerald-500 select-none">></span>
                            <span class="text-zinc-200">{{ $entry['line'] }}</span>
                        @else
                            <span class="text-zinc-400 whitespace-pre-wrap leading-normal">{{ $entry['line'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
            <div wire:loading wire:target="runCommand" class="mt-2 text-emerald-500/50 animate-pulse">_ Executing...</div>
        </div>

        {{-- Input --}}
        <div class="border-t border-zinc-800 bg-zinc-800 p-4">
            <form wire:submit.prevent="runCommand" class="flex items-center gap-3">
                <span class="font-mono text-emerald-500 font-bold select-none">›</span>
                <input type="text" wire:model="command" class="flex-1 bg-transparent border-none p-0 text-sm text-zinc-300 focus:ring-0 placeholder-zinc-700"
                       placeholder="Enter MikroTik command..." autocomplete="off" {{ $connectionStatus === 'disconnected' ? 'disabled' : '' }}>
            </form>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #27272a; border-radius: 10px; }
    </style>
</div>
