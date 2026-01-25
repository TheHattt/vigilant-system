<div class="overflow-hidden rounded-xl border border-white/5 bg-zinc-900/50">
    <table class="w-full text-left text-sm">
        <thead class="bg-white/5 text-[10px] uppercase tracking-widest text-zinc-500">
            <tr>
                <th class="px-4 py-3">Interface</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Download (RX)</th>
                <th class="px-4 py-3 text-right">Upload (TX)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @foreach($interfaces as $iface)
            <tr class="hover:bg-white/[0.02] transition-colors">
                <td class="px-4 py-3 font-medium text-zinc-200">{{ $iface['name'] }}</td>
                <td class="px-4 py-3">
                    @if($iface['running'] === 'true')
                        <span class="inline-flex items-center gap-1.5 text-emerald-500">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Active
                        </span>
                    @else
                        <span class="text-zinc-600 font-mono text-xs">Disconnected</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right font-mono text-emerald-400">
                    {{ $speeds[$iface['name']]['rx'] ?? '0 bps' }}
                </td>
                <td class="px-4 py-3 text-right font-mono text-blue-400">
                    {{ $speeds[$iface['name']]['tx'] ?? '0 bps' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
