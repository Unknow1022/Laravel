<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SecurityDemoController extends Controller
{
    /**
     * Alternar el estado de la protección XSS en la sesión.
     */
    public function toggleXss(Request $request)
    {
        $status = filter_var($request->input('status', true), FILTER_VALIDATE_BOOLEAN);
        session(['cortex_xss_protection' => $status]);

        return redirect()->back()->with('demo_status_changed', 'Protección XSS ' . ($status ? 'Activada' : 'Desactivada'));
    }

    /**
     * Alternar el estado de la protección SQL Injection en la sesión.
     */
    public function toggleSqli(Request $request)
    {
        $status = filter_var($request->input('status', true), FILTER_VALIDATE_BOOLEAN);
        session(['cortex_sqli_protection' => $status]);

        return redirect()->back()->with('demo_status_changed', 'Protección SQLi ' . ($status ? 'Activada' : 'Desactivada'));
    }
}
