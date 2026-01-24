<div>
    <form wire:submit="save" class="space-y-6 mt-6">
        <div>
            <h4 class="text-xs font-bold text-zinc-500 mb-4 uppercase tracking-widest">Basic Information</h4>
            <div class="space-y-4">
                <flux:input label="Display Name" wire:model="name" icon="computer-desktop" />
                <flux:input label="Management IP / Host" wire:model="host" icon="globe-alt" />
                <flux:input label="API Port" wire:model="api_port" icon="hashtag" />
            </div>
        </div>

        <div class="pt-6 border-t border-zinc-800 flex items-center justify-between">
            <flux:button
                variant="ghost"
                color="red"
                wire:click="delete"
                wire:confirm="Are you sure you want to delete this router? This cannot be undone."
                size="sm"
            >
                Delete Router
            </flux:button>

            <div class="flex gap-3">
                <flux:modal.close name="edit-router">
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="filled" color="indigo">
                    Save Changes
                </flux:button>
            </div>
        </div>
    </form>
</div>
