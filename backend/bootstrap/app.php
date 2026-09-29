<?php

use App\Http\Middleware\ProtectLeadData;
use App\Http\Middleware\RequireApiUser;
use App\Http\Middleware\RequireStaff;
use App\Http\Middleware\ResolveApiUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->api(append: [ResolveApiUser::class, ProtectLeadData::class]);
        $middleware->alias(['signed.in' => RequireApiUser::class, 'staff' => RequireStaff::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                \Illuminate\Support\Facades\Log::error('API Exception: '.$e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error' => basename($e->getFile()).':'.$e->getLine(),
                ], 500);
            }
        });
    })->create();
