<?php

namespace App\Livewire\Infrastructure\Router;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Router;
use App\Models\PppSecret;
use App\Services\MikrotikService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PppSecrets extends Component
{
    use WithPagination;

    public Router $router;

    // Filters
    public string $search = "";
    public string $serviceFilter = "";
    public string $statusFilter = "";

    // Selection
    public array $selectedSecrets = [];
    public bool $selectAll = false;

    // State
    public string $syncStatus = "idle";
    public ?string $syncError = null;

    // Form fields
    public ?int $editingId = null;
    public string $editName = "";
    public string $editPassword = "";
    public string $editProfile = "default";
    public string $editService = "pppoe";
    public string $editComment = "";

    protected $queryString = [
        "search" => ["except" => ""],
        "serviceFilter" => ["except" => ""],
        "statusFilter" => ["except" => ""],
    ];

    public function mount(Router $router): void
    {
        $this->router = $router;
    }

    public function updated($property): void
    {
        if (in_array($property, ["search", "serviceFilter", "statusFilter"])) {
            $this->resetPage();
        }
        if ($property === "selectAll") {
            $this->handleSelectAll();
        }
    }

    public function editSecret(int $id): void
    {
        $secret = PppSecret::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $secret->name;
        $this->editPassword = $secret->password ?? "";
        $this->editProfile = $secret->profile;
        $this->editService = $secret->service;
        $this->editComment = $secret->comment ?? "";

        $this->dispatch("modal-show", name: "edit-secret");
    }

    public function saveSecret(MikrotikService $mikrotikService): void
    {
        $this->validate([
            "editName" => "required",
            "editProfile" => "required",
        ]);

        try {
            $secret = PppSecret::findOrFail($this->editingId);

            DB::transaction(function () use ($mikrotikService, $secret) {
                $mikrotikService->updatePPPSecret($this->router, $secret, [
                    "name" => $this->editName,
                    "password" => $this->editPassword,
                    "profile" => $this->editProfile,
                    "service" => $this->editService,
                    "comment" => $this->editComment,
                ]);

                $secret->update([
                    "name" => $this->editName,
                    "password" => $this->editPassword,
                    "profile" => $this->editProfile,
                    "service" => $this->editService,
                    "comment" => $this->editComment,
                    "is_synced" => true,
                ]);
            });

            $this->dispatch("modal-hide", name: "edit-secret");
            $this->dispatch(
                "notify",
                type: "success",
                message: "Secret updated.",
            );
            $this->clearCache();
        } catch (\Exception $e) {
            $this->dispatch("notify", type: "error", message: $e->getMessage());
        }
    }

    public function syncNow(MikrotikService $mikrotikService): void
    {
        $this->syncStatus = "syncing";
        try {
            $mikrotikSecrets = $mikrotikService->getPPPSecrets($this->router);
            PppSecret::syncFromMikroTik($this->router->id, $mikrotikSecrets);
            $this->clearCache();
            $this->syncStatus = "idle";
            $this->dispatch(
                "notify",
                type: "success",
                message: "Synced successfully.",
            );
        } catch (\Exception $e) {
            $this->syncStatus = "error";
            $this->syncError = $e->getMessage();
        }
    }

    public function toggleActive(
        int $secretId,
        MikrotikService $mikrotikService,
    ): void {
        try {
            $secret = PppSecret::findOrFail($secretId);
            $newStatus = !$secret->is_active;
            $mikrotikService->updatePPPSecret($this->router, $secret, [
                "is_active" => $newStatus,
            ]);
            $secret->update(["is_active" => $newStatus, "is_synced" => true]);
            $this->clearCache();
        } catch (\Exception $e) {
            $this->dispatch("notify", type: "error", message: $e->getMessage());
        }
    }

    #[Computed]
    public function secrets(): LengthAwarePaginator
    {
        $query = PppSecret::where("router_id", $this->router->id);
        if ($this->search) {
            $query->where("name", "like", "%{$this->search}%");
        }
        return $query->orderByDesc("is_active")->paginate(20);
    }

    #[Computed]
    public function stats(): array
    {
        return Cache::remember(
            "ppp_stats_{$this->router->id}",
            30,
            fn() => [
                "total" => PppSecret::where(
                    "router_id",
                    $this->router->id,
                )->count(),
                "active" => PppSecret::where("router_id", $this->router->id)
                    ->where("is_active", true)
                    ->count(),
                "online" => PppSecret::where("router_id", $this->router->id)
                    ->where("last_connected_at", ">=", now()->subHours(24))
                    ->count(),
                "needs_sync" => PppSecret::where("router_id", $this->router->id)
                    ->where("is_synced", false)
                    ->count(),
            ],
        );
    }

    public function render(): View
    {
        return view("livewire.infrastructure.router.ppp-secrets", [
            "lastSyncedAt" => PppSecret::where(
                "router_id",
                $this->router->id,
            )->max("last_synced_at"),
        ]);
    }

    protected function handleSelectAll(): void
    {
        $this->selectedSecrets = $this->selectAll
            ? collect($this->secrets->items())
                ->pluck("id")
                ->map(fn($id) => (string) $id)
                ->toArray()
            : [];
    }

    protected function clearCache(): void
    {
        Cache::forget("ppp_stats_{$this->router->id}");
        unset($this->stats, $this->secrets);
    }
}
