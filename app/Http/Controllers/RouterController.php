<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function index()
    {
        $routers = Router::where("tenant_id", Auth::user()->tenant_id)
            ->with("site")
            ->latest()
            ->get();

        return view("routers.index", compact("routers"));
    }

    public function show(Router $router)
    {
        if (
            $router->tenant_id !== Auth::user()->tenant_id &&
            !Auth::user()->is_admin &&
            !Auth::user()->is_super_admin
        ) {
            abort(403, "Unauthorized access.");
        }

        return view("routers.show", compact("router"));
    }

    public function export(Router $router, MikrotikService $service)
    {
        if (
            $router->tenant_id !== Auth::user()->tenant_id &&
            !Auth::user()->is_admin
        ) {
            abort(403);
        }

        try {
            // Get the export content first to ensure the connection is alive
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
            // This prevents ERR_INVALID_RESPONSE by returning a proper Laravel redirect
            return back()->with("error", "Export failed: " . $e->getMessage());
        }
    }
}
