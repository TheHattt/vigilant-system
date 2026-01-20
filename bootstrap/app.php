<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // No special method needed here for the throttle
        // Just keep it clean
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // This is the "Stone-Proof" way to catch the 429 and turn it into a redirect
        $exceptions->render(function (
            \Illuminate\Http\Exceptions\ThrottleRequestsException $e,
            Request $request,
        ) {
            if ($request->is("login") || $request->is("*/login")) {
                throw ValidationException::withMessages([
                    "email" => [
                        __("auth.throttle", [
                            "seconds" => $e->getHeaders()["Retry-After"] ?? 60,
                        ]),
                    ],
                ]);
            }
        });
    })
    ->create();
