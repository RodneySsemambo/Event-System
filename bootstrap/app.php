<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enables Sanctum's SPA session auth on API routes. Without this,
        // any auth:sanctum route will see the request as a guest — the
        // session cookie is never read.
        $middleware->statefulApi();

        // API requests should get a 401 JSON response when unauthenticated,
        // not a redirect to a web 'login' route that doesn't exist.
        $middleware->redirectGuestsTo(fn() => null);

        // Registers the 'role:...' route middleware alias used in
        // routes/api.php (e.g. ->middleware('role:provider')).
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403 && $request->is('admin*')) {
                return response()->view('errors.403', [], 403);
            }
        });
    })

    ->create();
