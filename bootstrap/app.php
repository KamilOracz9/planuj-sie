<?php

use App\Http\Middleware\ApiKeyMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Spatie\ResponseCache\Middlewares\CacheResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);
        // Scoped to the 'api' group (not global) so it doesn't touch
        // routes/web.php or the /up health check, and so a specific route
        // (media.show - see routes/api.php) can opt out via
        // ->withoutMiddleware(): that only works for group/route
        // middleware, not middleware prepended truly globally.
        $middleware->api(prepend: [ApiKeyMiddleware::class]);
        // Caches every safe (GET, 200) response in the API - see the
        // caching-strategy skill. Scoped to the 'api' group (not global)
        // so it doesn't touch routes/web.php or the /up health check.
        $middleware->api(append: [CacheResponse::class]);

        // `append()` above only controls array order, not actual execution
        // order - Laravel re-sorts the pipeline by $middlewarePriority, and
        // CacheResponse isn't in that list by default, so without this it
        // was observed running BEFORE auth:api: an unauthenticated request
        // could be served an already-cached response from an authenticated
        // one (a real auth bypass), or vice versa. This pins it to always
        // run after Authenticate.
        $middleware->appendToPriorityList(after: Authenticate::class, append: CacheResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // This is a JSON-only API with no web login page. Laravel's default
        // unauthenticated-guest handling redirects to route('login') for any
        // request that doesn't explicitly send an Accept: application/json
        // header (expectsJson() is false) - which crashes with
        // RouteNotFoundException on a client that just omits that header
        // (e.g. a bare curl/Postman call), instead of a clean 401. Always
        // respond with JSON here regardless of what the client asked for.
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json(['message' => $e->getMessage()], 401);
        });
    })->create();
