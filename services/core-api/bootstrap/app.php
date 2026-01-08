<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.apikey' => \App\Http\Middleware\EnsureApiKeyIsValid::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            Log::warning('Método não permitido', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip()
            ]);

            return response()->json(['message' => 'Método HTTP não permitido para este endpoint.'], 405);    
        });

        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            Log::notice('Endpoint não encontrado', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip()
            ]);

            return response()->json(['message' => 'Endpoint não encontrado. Verifique a URL.'], 404);
        });

        $exceptions->renderable(function (Throwable $e, $request) {
            Log::error('Erro interno no servidor', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ]);

            return response()->json([
                'message' => 'Erro interno no servidor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        });
    })->create();
