<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XssSanitization
{
    /**
     * Handle an incoming request.
     * Sanitizes inputs against XSS scripts only if the sandbox protection is enabled.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // La protección XSS está activada por defecto (true) a menos que se apague explícitamente en la sesión
        $protectionActive = session()->get('cortex_xss_protection', true);

        if ($protectionActive) {
            $input = $request->all();

            // Sanitizar de forma recursiva todos los datos de entrada tipo string
            array_walk_recursive($input, function (&$val) {
                if (is_string($val)) {
                    // strip_tags remueve tags HTML y PHP, neutralizando <script> u otras etiquetas inyectadas
                    $val = strip_tags($val);
                }
            });

            $request->merge($input);
        }

        return $next($request);
    }
}
