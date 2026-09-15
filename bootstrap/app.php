<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

// Nota: La consola de rescate de Cortex está disponible solo en localhost.

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies for secure HTTPS tunnels (Pinggy/Ngrok/InfinityFree Cloudflare)
        $middleware->trustProxies(at: '*');

        // Global security headers on every response
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // XSS sanitization running inside the web group (with session booted)
        $middleware->web(append: [
            \App\Http\Middleware\XssSanitization::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejo amigable de sesión/CSRF expirado (Previene error 419 Page Expired)
        $exceptions->render(function (TokenMismatchException $e, $request) {
            return redirect()->route('login')->withErrors([
                'usuario' => 'La sesión o el formulario ha caducado. Por favor, vuelve a ingresar tu usuario y contraseña.'
            ]);
        });
    })->create();
