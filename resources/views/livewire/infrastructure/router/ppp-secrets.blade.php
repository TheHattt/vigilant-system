<div class="p-8">
    {{-- Header Section --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <flux:heading size="xl" level="1" class="tracking-tight text-zinc-900 dark:text-white">{{ $router->name }} <span class="text-zinc-400 mx-2">—</span> PPP Secrets</flux:heading>
            <div class="flex items-center gap-2 mt-1">
                <flux:subheading class="flex items-center gap-2">
                    <flux:icon.server variant="mini" class="text-zinc-400" />
                    {{ $router->hostname }}
                </flux:subheading>
                @if(Cache::has("router.{$router->id}.unreachable"))
                    <flux:badge color="red" size="sm" class="animate-pulse">Router Unreachable</flux:badge>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <flux:button
                wire:click="syncNow"
                icon="arrow-path"
                wire:loading.attr="disabled"
                wire:target="syncNow"
                :loading="$syncStatus === 'syncing'"
                variant="subtle"
            >
                Sync All
            </flux:button>
            <flux:separator vertical class="h-6 mx-1" />
            <flux:modal.trigger name="create-secret">
                <flux:button variant="filled" color="zinc" icon="plus">Create User</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        @foreach([
            ['label' => 'Total', 'value' => $this->stats['total'], 'color' => 'zinc'],
            ['label' => 'Online', 'value' => $this->stats['online'], 'color' => 'green'],
            ['label' => 'Offline', 'value' => $this->stats['total'] - $this->stats['online'], 'color' => 'zinc'],
            ['label' => 'Active', 'value' => $this->stats['active'], 'color' => 'zinc'],
            ['label' => 'Pending', 'value' => $this->stats['needs_sync'], 'color' => 'amber'],
        ] as $stat)
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-widest text-{{ $stat['color'] }}-600/80 mb-1">{{ $stat['label'] }}</div>
            <div class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $stat['value'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Bulk Actions Bar --}}
    <div class="mb-4 h-12">
        @if(count($selected) > 0)
            <div class="flex items-center gap-4 px-4 py-2 bg-zinc-900 dark:bg-zinc-800 text-white rounded-xl shadow-lg animate-in fade-in slide-in-from-top-2 duration-200">
                <div class="flex items-center gap-2 border-r border-white/10 pr-4">
                    <span class="text-sm font-medium">{{ count($selected) }} items selected</span>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button wire:click="bulkSync" size="sm" variant="ghost" class="text-white hover:bg-white/10" icon="arrow-path">Sync Selected</flux:button>
                    <flux:modal.trigger name="confirm-bulk-delete">
                        <flux:button size="sm" variant="ghost" class="text-red-400 hover:bg-red-400/10 hover:text-red-300" icon="trash">Delete Selected</flux:button>
                    </flux:modal.trigger>
                </div>
                <flux:spacer />
                <flux:button wire:click="$set('selected', [])" size="sm" variant="ghost" class="text-zinc-400 hover:text-white">Clear Selection</flux:button>
            </div>
        @else
            <div class="flex flex-col sm:flex-row gap-3">
                <flux:input wire:model.live.debounce.400ms="search" icon="magnifying-glass" placeholder="Filter by name or comment..." class="flex-1" />
                <flux:select wire:model.live="statusFilter" class="sm:w-48">
                    <flux:select.option value="">All Connections</flux:select.option>
                    <flux:select.option value="active">Active Only</flux:select.option>
                    <flux:select.option value="inactive">Disabled Only</flux:select.option>
                </flux:select>
            </div>
        @endif
    </div>

    {{-- Main Table --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-800">
                    <th class="pl-6 py-3 w-10">
                        <flux:checkbox wire:model.live="selectAll" />
                    </th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-zinc-500">User Details</th>
                    <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-zinc-500">Network</th>
                    <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-zinc-500">Status</th>
                    <th class="px-4 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-zinc-500">Health</th>
                    <th class="px-6 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-zinc-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($this->secrets as $secret)
                    @php $isOnline = in_array($secret->name, $this->onlineUsernames); @endphp
                    <tr class="group hover:bg-zinc-50/50 dark:hover:bg-white/[0.02] transition-colors {{ in_array((string)$secret->id, $selected) ? 'bg-zinc-50 dark:bg-zinc-800/40' : '' }}">
                        <td class="pl-6 py-4">
                            <flux:checkbox wire:model.live="selected" value="{{ (string)$secret->id }}" />
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div @class([
                                    'h-9 w-9 rounded-lg flex items-center justify-center font-bold text-xs shadow-sm border',
                                    'bg-green-50 border-green-100 text-green-700' => $isOnline,
                                    'bg-zinc-50 border-zinc-100 text-zinc-500' => !$isOnline,
                                ])>
                                    {{ strtoupper(substr($secret->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-white text-sm">{{ $secret->name }}</div>
                                    <div class="text-xs text-zinc-500 truncate max-w-[200px]">{{ $secret->comment ?: 'No description provided' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase">{{ $secret->service }}</span>
                                <span class="text-[11px] font-mono text-zinc-500">{{ $secret->profile }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($isOnline)
                                <flux:badge color="green" size="sm" inset="top bottom" class="uppercase text-[10px] font-bold tracking-tighter">Online</flux:badge>
                            @else
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Offline</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center">
                            @if($secret->is_synced)
                                <div class="flex items-center justify-center text-green-500" title="Synced with Router">
                                    <flux:icon.check-circle variant="mini" />
                                </div>
                            @else
                                <div class="flex items-center justify-center text-amber-500 animate-pulse" title="Sync Pending">
                                    <flux:icon.arrow-path variant="mini" />
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <flux:button wire:click="editSecret({{ $secret->id }})" variant="ghost" size="sm" icon="pencil-square" />
                                <flux:modal.trigger name="confirm-delete">
                                    <flux:button wire:click="$set('editingId', {{ $secret->id }})" variant="ghost" size="sm" icon="trash" color="red" />
                                </flux:modal.trigger>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-24 text-center">
                            <flux:icon.users class="mx-auto h-12 w-12 text-zinc-200 mb-4" />
                            <flux:heading>No users matches your criteria</flux:heading>
                            <flux:subheading>Try resetting your filters or adding a new user.</flux:subheading>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- --- ENTERPRISE MODALS --- --}}

    {{-- Create Modal --}}
    <flux:modal name="create-secret" class="md:w-[550px] space-y-6">
        <div>
            <flux:heading size="lg">Add Network User</flux:heading>
            <flux:subheading>Register a new PPP secret to the MikroTik database.</flux:subheading>
        </div>

        <form wire:submit="createSecret" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <flux:input label="Username" wire:model="newName" placeholder="e.g. client_01" required />
                <flux:input label="Password" type="password" wire:model="newPassword" viewable required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="Service Type" wire:model="newService">
                    <flux:select.option value="pppoe">PPPoE</flux:select.option>
                    <flux:select.option value="any">Any</flux:select.option>
                </flux:select>
                <flux:input label="Profile Name" wire:model="newProfile" placeholder="default" />
            </div>

            <flux:textarea label="Administrative Comment" wire:model="newComment" rows="2" placeholder="Customer location or ID..." />

            <div class="flex gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="filled" color="zinc">Confirm & Add User</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal name="edit-secret" class="md:w-[550px] space-y-6">
        <div>
            <flux:heading size="lg">Edit Network User</flux:heading>
            {{-- Using editName here matches your PHP property --}}
            <flux:subheading>Modify credentials or profile for <span class="font-bold text-zinc-900 dark:text-white">{{ $editName }}</span>.</flux:subheading>
        </div>

        <form wire:submit="updateSecret" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                {{-- Updated wire:models to match your editSecret function --}}
                <flux:input label="Username" wire:model="editName" required />
                <flux:input label="Password" type="password" wire:model="editPassword" viewable placeholder="Leave blank to keep current" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select label="Service Type" wire:model="editService">
                    <flux:select.option value="pppoe">PPPoE</flux:select.option>
                    <flux:select.option value="any">Any</flux:select.option>
                </flux:select>
                <flux:input label="Profile Name" wire:model="editProfile" />
            </div>

            <flux:textarea label="Administrative Comment" wire:model="editComment" rows="2" />

            <div class="flex gap-2 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">Cancel</flux:button></flux:modal.close>
                <flux:button type="submit" variant="filled" color="zinc">Save Changes</flux:button>
            </div>
        </form>
    </flux:modal>    {{-- Bulk Delete Confirmation --}}
    <flux:modal name="confirm-bulk-delete" class="md:w-[450px] space-y-6">
        <div class="flex flex-col items-center text-center">
            <div class="h-14 w-14 bg-red-50 text-red-600 rounded-full flex items-center justify-center mb-4">
                <flux:icon.exclamation-triangle size="lg" />
            </div>
            <flux:heading size="lg">Destructive Action</flux:heading>
            <p class="text-sm text-zinc-500 mt-2">You are about to delete <strong>{{ count($selected) }}</strong> users. This will remove them from the router immediately.</p>
        </div>
        <div class="flex flex-col gap-2">
            <flux:button wire:click="bulkDelete" variant="filled" color="red">Delete Records Forever</flux:button>
            <flux:modal.close><flux:button variant="ghost" class="w-full">Dismiss</flux:button></flux:modal.close>
        </div>
    </flux:modal>
    {{-- Single Delete Confirmation --}}
        <flux:modal name="confirm-delete" class="md:w-[450px] space-y-6">
            <div class="flex flex-col items-center text-center">
                {{-- Warning Icon with a soft red glow --}}
                <div class="h-16 w-16 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-500 rounded-full flex items-center justify-center mb-4 shadow-sm">
                    <flux:icon.trash size="lg" variant="outline" />
                </div>

                <div>
                    <flux:heading size="lg">Confirm User Deletion</flux:heading>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2 px-4">
                        Are you sure you want to remove this user? This will disconnect the active session and remove the secret from the <b>{{ $router->name }}</b> router.
                    </p>
                </div>
            </div>

            {{-- Row highlighting what is being deleted --}}
            <div class="bg-zinc-50 dark:bg-white/[0.03] border border-zinc-100 dark:border-zinc-800 rounded-lg p-3 flex items-center gap-3">
                <div class="h-8 w-8 bg-zinc-200 dark:bg-zinc-700 rounded flex items-center justify-center text-xs font-bold text-zinc-600 dark:text-zinc-400">
                    ID
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-zinc-400 uppercase tracking-tight">Deleting ID</span>
                    <span class="text-sm font-mono text-zinc-900 dark:text-white">{{ $editingId ?: 'N/A' }}</span>
                </div>
            </div>

            <div class="flex flex-col gap-2 pt-2">
                <flux:button
                    wire:click="deleteSecret({{ $editingId }})"
                    variant="filled"
                    color="red"
                    class="w-full"
                >
                    Confirm Delete
                </flux:button>

                <flux:modal.close class="w-full">
                    <flux:button variant="ghost" class="w-full text-zinc-500">
                        Cancel, keep user
                    </flux:button>
                </flux:modal.close>
            </div>
        </flux:modal>

    {{-- Toast Replacement --}}
    @persist('notifications')
    <div x-data="{
        notifications: [],
        add(e) {
            const id = Date.now();
            this.notifications.push({ id, ...e.detail });
            setTimeout(() => this.notifications = this.notifications.filter(n => n.id !== id), 5000);
        }
    }" @toast.window="add($event)" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2">
        <template x-for="n in notifications" :key="n.id">
            <div x-transition class="bg-zinc-900 text-white px-4 py-3 rounded-xl shadow-2xl border border-white/10 flex items-center gap-3 min-w-[300px]">
                <div class="h-2 w-2 rounded-full bg-green-400"></div>
                <div class="flex-1 text-sm font-medium" x-text="n.heading || n.text"></div>
            </div>
        </template>
    </div>
    @endpersist
</div>
