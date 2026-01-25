<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RouterController extends Controller
{
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Display the dashboard with cached statistics and eager-loaded routers.
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $cacheKey = "routers.tenant.{$tenantId}.v2";

        $routers = Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => Router::where("tenant_id", $tenantId)
                ->with("site:id,name")
                ->withCount([
                    "pppSecrets as pending_sync_count" => fn(
                        $query,
                    ) => $query->where("is_synced", false),
                ])
                ->latest()
                ->get(),
        );

        return view("routers.index", compact("routers"));
    }

    /**
     * Show a single router with its secrets and live session data.
     */
    public function show(Router $router)
    {
        $this->authorizeRouterAccess($router, "view");

        $cacheKey = "router.{$router->id}.details.v2";

        $router = Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => $router->load([
                "site:id,name",
                "pppSecrets" => fn($query) => $query->select(
                    "id",
                    "router_id",
                    "name",
                    "service",
                    "is_active",
                    "is_synced",
                ),
                "pppSecrets.liveSessions",
            ]),
        );

        return view("routers.show", compact("router"));
    }

    public function export(Router $router, MikrotikService $service)
    {
        $this->authorizeRouterAccess($router, "export");

        try {
            $config = $service->getExport($router);

            if (empty($config)) {
                throw new \Exception("Router returned empty configuration.");
            }

            $filename =
                "{$router->name}-config-" . now()->format("Y-m-d") . ".rsc";

            return response($config, 200, [
                "Content-Type" => "text/plain",
                "Content-Disposition" =>
                    'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error(
                "Export failed for Router {$router->id}: " . $e->getMessage(),
            );
            return back()->with("error", "Export failed: " . $e->getMessage());
        }
    }

    private function authorizeRouterAccess(Router $router, string $action): void
    {
        $user = Auth::user();

        if ($router->tenant_id !== $user->tenant_id) {
            if (!$user->is_admin && !$user->is_super_admin) {
                abort(403, "Unauthorized access.");
            }
        }
    }

    public static function invalidateCache(Router $router): void
    {
        Cache::forget("router.{$router->id}.details.v2");
        Cache::forget("routers.tenant.{$router->tenant_id}.v2");
    }
}
