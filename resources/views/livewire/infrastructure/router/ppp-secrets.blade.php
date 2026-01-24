<div class="space-y-6">
    {{-- Sync Banners --}}
    @if($syncStatus === 'syncing')
        <div class="rounded-lg bg-indigo-900/20 border border-indigo-500/50 p-4">
            <div class="flex items-center gap-3">
                <flux:icon.arrow-path class="animate-spin h-5 w-5 text-indigo-400" />
                <p class="text-sm text-indigo-400 font-semibold">Syncing with MikroTik...</p>
            </div>
        </div>
    @elseif($syncStatus === 'error')
        <div class="rounded-lg bg-red-900/20 border border-red-500/50 p-4 flex justify-between items-center">
            <p class="text-sm text-red-400">{{ $syncError }}</p>
            <flux:button size="sm" variant="ghost" wire:click="syncNow" class="text-red-400">Retry</flux:button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @foreach(['Total' => 'total', 'Active' => 'active', 'Live' => 'online', 'Pending' => 'needs_sync'] as $label => $key)
            <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-5">
                <p class="text-[10px] font-bold uppercase text-zinc-500">{{ $label }}</p>
                <p class="text-2xl font-semibold text-white">{{ $this->stats[$key] ?? 0 }}</p>
            </div>
        @endforeach
    </div>

    {{-- Search and Actions --}}
    <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by username or comment..." class="w-full bg-zinc-900 border-zinc-800 rounded-lg text-white px-4 py-2 focus:ring-1 focus:ring-white/20 outline-none" />
        </div>
        <div class="flex gap-2">
            <flux:button icon="arrow-path" wire:click="syncNow" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="syncNow">Sync</span>
                <span wire:loading wire:target="syncNow">Syncing...</span>
            </flux:button>
            <flux:button icon="plus" variant="filled" class="!bg-white !text-black" x-on:click="$flux.modal('create-secret').show()">New Secret</flux:button>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-900/80 border-b border-zinc-800 text-zinc-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="p-4 w-12"><input type="checkbox" wire:model.live="selectAll" class="rounded border-zinc-700 bg-zinc-800 text-white" /></th>
                        <th class="px-6 py-3">User / Service</th>
                        <th class="px-6 py-3">Profile</th>
                        <th class="px-6 py-3">Addresses (L/R)</th>
                        <th class="px-6 py-3">Last Caller ID</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/50">
                    @forelse($this->secrets as $secret)
                        <tr class="group hover:bg-white/[0.02] transition-colors">
                            <td class="p-4"><input type="checkbox" wire:model.live="selectedSecrets" value="{{ $secret->id }}" class="rounded border-zinc-700 bg-zinc-800" /></td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-white font-medium">{{ $secret->name }}</span>
                                    <span class="text-[10px] text-zinc-500 uppercase">{{ $secret->service }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-zinc-400">{{ $secret->profile }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-zinc-400">
                                    <div>L: {{ $secret->local_address ?: '---' }}</div>
                                    <div>R: {{ $secret->remote_address ?: '---' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-zinc-500">
                                {{ $secret->last_caller_id ?: 'Never' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($secret->is_active)
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Enabled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-full text-[10px] font-medium bg-zinc-500/10 text-zinc-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span> Disabled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="editSecret({{ $secret->id }})" />
                                    <flux:button variant="ghost" size="sm"
                                        icon="{{ $secret->is_active ? 'pause' : 'play' }}"
                                        wire:click="toggleActive({{ $secret->id }})"
                                        class="{{ $secret->is_active ? 'text-amber-400' : 'text-emerald-400' }}" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <flux:icon.document-magnifying-glass class="h-8 w-8 text-zinc-700" />
                                    <p class="text-zinc-500 text-sm">No secrets found for this router.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($this->secrets->hasPages())
            <div class="p-4 border-t border-zinc-800 bg-zinc-900/20">
                {{ $this->secrets->links() }}
            </div>
        @endif
    </div>

    {{-- Edit Modal --}}
    <flux:modal name="edit-secret" class="min-w-[450px]">
        <form wire:submit="saveSecret" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit PPP Secret</flux:heading>
                <flux:subheading>Update credentials for {{ $editName }}</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input label="Username" wire:model="editName" required />
                <flux:input label="Password" type="password" wire:model="editPassword" viewable />

                <div class="grid grid-cols-2 gap-4">
                    <flux:select label="Service" wire:model="editService">
                        <option value="pppoe">PPPoE</option>
                        <option value="pptpd">PPTP</option>
                        <option value="l2tp">L2TP</option>
                        <option value="any">Any</option>
                    </flux:select>
                    <flux:input label="Profile" wire:model="editProfile" placeholder="default" />
                </div>

                <flux:textarea label="Comment (Internal)" wire:model="editComment" rows="2" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="filled" class="!bg-white !text-black">Save Changes</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
