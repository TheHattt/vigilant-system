<?php

namespace App\Livewire\Infrastructure\Router;

use App\Models\Router;
use App\Models\PppSecret;
use App\Models\ActivityLog;
use App\Services\MikrotikService;
use App\Jobs\SyncRouterSecretsBatch;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PppSecrets extends Component
{
    use WithPagination;

    // --- State Properties ---
    public Router $router;
    public string $search = "";
    public string $statusFilter = "";
    public string $syncStatus = "idle";
    public string $syncError = "";

    // Bulk Actions
    public array $selected = [];
    public bool $selectAll = false;

    // Form Properties (Create/Edit)
    public string $newName = "",
        $newPassword = "",
        $newService = "pppoe",
        $newProfile = "default",
        $newComment = "";
    public ?int $editingId = null;
    public string $editName = "",
        $editPassword = "",
        $editService = "pppoe",
        $editProfile = "default",
        $editComment = "";

    // --- Caching for Stats ---
    private ?array $cachedOnlineUsernames = null;
    private ?array $cachedStats = null;
    private int $cacheTimestamp = 0;
    private const CACHE_DURATION = 30;

    public function mount(Router $router): void
    {
        $this->router = $router;
    }

    // --- Computed Properties (Fixes the $stats error) ---

    #[Computed]
    public function stats()
    {
        $baseQuery = PppSecret::where("router_id", $this->router->id);
        return [
            "total" => (clone $baseQuery)->count(),
            "active" => (clone $baseQuery)->where("is_active", true)->count(),
            "online" => count($this->onlineUsernames),
            "needs_sync" => (clone $baseQuery)
                ->where("is_synced", false)
                ->count(),
        ];
    }

    #[Computed]
    public function onlineUsernames()
    {
        $breakerKey = "router.{$this->router->id}.unreachable";
        if (Cache::has($breakerKey) || !$this->router->is_online) {
            return [];
        }

        return Cache::remember(
            "router.{$this->router->id}.active_list",
            30,
            function () use ($breakerKey) {
                try {
                    $active = app(MikrotikService::class)->getActiveConnections(
                        $this->router,
                        silent: true,
                    );
                    return collect($active)->pluck("name")->toArray();
                } catch (\Exception $e) {
                    Cache::put($breakerKey, true, now()->addMinutes(5));
                    return [];
                }
            },
        );
    }

    #[Computed]
    public function secrets()
    {
        return PppSecret::where("router_id", $this->router->id)
            ->when(
                $this->search,
                fn($q) => $q->where(
                    fn($sub) => $sub
                        ->where("name", "like", "%{$this->search}%")
                        ->orWhere("comment", "like", "%{$this->search}%"),
                ),
            )
            ->when(
                $this->statusFilter !== "",
                fn($q) => $q->where(
                    "is_active",
                    $this->statusFilter === "active",
                ),
            )
            ->latest()
            ->paginate(15);
    }

    // --- CRUD Actions with Change Tracking ---

    public function createSecret(): void
    {
        $this->validate([
            "newName" => "required|min:2",
            "newPassword" => "required|min:4",
        ]);

        $secret = PppSecret::create([
            "router_id" => $this->router->id,
            "name" => $this->newName,
            "password" => $this->newPassword,
            "service" => $this->newService,
            "profile" => $this->newProfile,
            "comment" => $this->newComment,
            "is_active" => true,
            "is_synced" => false,
        ]);

        ActivityLog::create([
            "router_id" => $this->router->id,
            "user_id" => auth()->id(),
            "action" => "create",
            "description" => "Created PPP Secret: {$this->newName} (Profile: {$this->newProfile})",
        ]);

        $this->syncToHardware($secret);
        $this->reset(["newName", "newPassword", "newComment"]);
        $this->dispatch("modal-close", name: "create-secret");
    }

    public function updateSecret(): void
    {
        $secret = PppSecret::findOrFail($this->editingId);
        $oldValues = $secret->only(["name", "profile", "comment"]);

        $secret->update([
            "name" => $this->editName,
            "password" => $this->editPassword,
            "service" => $this->editService,
            "profile" => $this->editProfile,
            "comment" => $this->editComment,
            "is_synced" => false,
        ]);

        $changes = array_diff_assoc(
            $secret->only(["name", "profile", "comment"]),
            $oldValues,
        );
        $diffText = collect($changes)
            ->map(fn($v, $k) => "$k to '$v'")
            ->implode(", ");

        ActivityLog::create([
            "router_id" => $this->router->id,
            "user_id" => auth()->id(),
            "action" => "update",
            "description" =>
                "Updated {$secret->name}" . ($diffText ? " ($diffText)" : ""),
            "changes" => json_encode(["from" => $oldValues, "to" => $changes]),
        ]);

        $this->syncToHardware($secret);
        $this->dispatch("modal-close", name: "edit-secret");
    }

    public function toggleActive($id): void
    {
        $secret = PppSecret::findOrFail($id);
        $newStatus = !$secret->is_active;
        $label = $newStatus ? "Enabled" : "Disabled";

        $secret->update(["is_active" => $newStatus, "is_synced" => false]);

        ActivityLog::create([
            "router_id" => $this->router->id,
            "user_id" => auth()->id(),
            "action" => "toggle",
            "description" => "{$label} PPP Secret: {$secret->name}",
        ]);

        $this->syncToHardware($secret, ["is_active" => $newStatus]);
    }

    public function deleteSecret($id = null): void
    {
        $secret = PppSecret::findOrFail($id ?? $this->editingId);
        $name = $secret->name;

        ActivityLog::create([
            "router_id" => $this->router->id,
            "user_id" => auth()->id(),
            "action" => "delete",
            "description" => "Deleted PPP Secret: {$name}",
        ]);

        try {
            app(MikrotikService::class)->deletePPPSecret(
                $this->router,
                $secret,
            );
        } catch (\Exception $e) {
        }

        $secret->delete();
        $this->dispatch("modal-close", name: "confirm-delete");
    }

    private function syncToHardware($secret, $data = null)
    {
        if ($this->router->is_online) {
            try {
                app(MikrotikService::class)->updatePPPSecret(
                    $this->router,
                    $secret,
                    $data ?? $secret->toArray(),
                );
                $secret->update(["is_synced" => true]);
            } catch (\Exception $e) {
                Log::warning("Hardware sync delayed for {$secret->name}");
            }
        }
    }

    public function render()
    {
        return view("livewire.infrastructure.router.ppp-secrets");
    }
}
