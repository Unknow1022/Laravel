<?php
/**
 * Diagnóstico completo del sistema de correo de herramientas agotadas.
 * Abre: https://almacen-inteligente.infinityfreeapp.com/public/test_stock_mail.php
 */

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\HerramientaAgotadaMail;
use App\Models\Herramienta;
use App\Models\ValeDetalle;
use Carbon\Carbon;

$request  = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$kernel->terminate($request, $response);

header('Content-Type: text/html; charset=utf-8');

$s = fn(string $c, string $t) => "<span style='color:{$c};font-weight:bold;'>{$t}</span>";
$ok  = fn(string $t) => "<div style='color:#00ff88;margin:4px 0;'>✓ {$t}</div>";
$err = fn(string $t) => "<div style='color:#ff4444;margin:4px 0;'>✗ {$t}</div>";
$inf = fn(string $t) => "<div style='color:#64ffda;margin:4px 0;'>ℹ {$t}</div>";

echo <<<HTML
<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Diagnóstico Stock Mail</title>
<style>
body{background:#070f1a;color:#cfd8e3;font-family:monospace;padding:24px;font-size:14px;line-height:1.7}
h2{color:#64ffda}h3{color:#e6f1ff;border-bottom:1px solid #1e293b;padding-bottom:6px}
pre{background:#112240;padding:16px;border-radius:8px;border:1px solid #1e3a5f;overflow-x:auto;white-space:pre-wrap}
.card{background:#0f172a;border:1px solid #1e293b;border-radius:10px;padding:18px;margin-bottom:18px}
table{border-collapse:collapse;width:100%}td,th{padding:8px 12px;border:1px solid #1e293b;font-size:13px}
th{background:#112240;color:#64ffda}td:first-child{color:#94a3b8;width:35%}
</style></head><body>
<h2>🔍 Diagnóstico — Sistema de Alertas de Stock</h2>
HTML;

// ─── PASO 1: Verificar clase mail ───────────────────────────────────────────
echo "<h3>Paso 1: Clases Mail</h3><div class='card'>";
if (class_exists('App\Mail\HerramientaAgotadaMail')) {
    echo $ok("HerramientaAgotadaMail existe");
} else {
    echo $err("HerramientaAgotadaMail NO existe → subir_fix.php no se ejecutó");
}
if (file_exists(__DIR__ . '/../resources/views/emails/herramienta_agotada.blade.php')) {
    echo $ok("Template herramienta_agotada.blade.php existe");
} else {
    echo $err("Template herramienta_agotada.blade.php NO existe");
}
echo "</div>";

// ─── PASO 2: Herramientas agotadas ─────────────────────────────────────────
echo "<h3>Paso 2: Herramientas Agotadas / Críticas</h3><div class='card'>";
try {
    $agotadas = Herramienta::where('stock_disponible', '<=', 0)->get();
    $criticas  = Herramienta::where('stock_disponible', '>', 0)
        ->whereColumn('stock_disponible', '<=', 'stock_minimo')->get();

    echo $inf("Herramientas agotadas (stock=0): <b>{$agotadas->count()}</b>");
    echo $inf("Herramientas críticas (≤mínimo): <b>{$criticas->count()}</b>");

    $todas = $agotadas->merge($criticas);
    if ($todas->count() > 0) {
        echo "<table style='margin-top:10px;'><tr><th>Código</th><th>Nombre</th><th>Disp</th><th>Total</th><th>Mínimo</th><th>Tipo</th></tr>";
        foreach ($todas as $h) {
            $tipo = $h->stock_disponible <= 0 ? '🔴 AGOTADA' : '🟡 CRÍTICA';
            echo "<tr><td>{$h->codigo}</td><td>{$h->nombre}</td><td>{$h->stock_disponible}</td><td>{$h->stock_total}</td><td>{$h->stock_minimo}</td><td>{$tipo}</td></tr>";
        }
        echo "</table>";
    } else {
        echo $inf("No hay herramientas agotadas ni críticas actualmente.");
    }
} catch (\Exception $e) {
    echo $err("Error al consultar herramientas: " . htmlspecialchars($e->getMessage()));
}
echo "</div>";

// ─── PASO 3: Trabajadores con préstamos activos de la herramienta agotada ──
echo "<h3>Paso 3: Trabajadores con Préstamos Activos</h3><div class='card'>";
try {
    $herramientaPrueba = Herramienta::where('stock_disponible', '<=', 0)->first()
        ?? Herramienta::where('stock_disponible', '>', 0)->whereColumn('stock_disponible', '<=', 'stock_minimo')->first();

    if ($herramientaPrueba) {
        $detalles = ValeDetalle::where('herramienta_id', $herramientaPrueba->id)
            ->whereHas('vale', fn($q) => $q->where('estado', 'Activo'))
            ->with(['vale.trabajador'])
            ->get();

        echo $inf("Herramienta seleccionada para prueba: <b>{$herramientaPrueba->nombre}</b> ({$herramientaPrueba->codigo})");
        echo $inf("Detalles de vales activos encontrados: <b>{$detalles->count()}</b>");

        if ($detalles->count() > 0) {
            echo "<table style='margin-top:10px;'><tr><th>Trabajador</th><th>DNI</th><th>Vale</th><th>Cantidad</th><th>Fecha Límite</th></tr>";
            foreach ($detalles as $d) {
                $t = optional($d->vale)->trabajador;
                $nombre = $t ? $t->nombre . ' ' . $t->apellidos : 'Desconocido';
                $dni = optional($t)->dni ?? '—';
                $vale = optional($d->vale)->codigo_vale ?? '—';
                $lim  = optional($d->vale)->fecha_limite ? Carbon::parse($d->vale->fecha_limite)->format('d/m/Y') : '—';
                echo "<tr><td>$nombre</td><td>$dni</td><td>$vale</td><td>{$d->cantidad_prestada}</td><td>$lim</td></tr>";
            }
            echo "</table>";
        }
    } else {
        echo $inf("No se encontró ninguna herramienta agotada/crítica para probar.");
    }
} catch (\Exception $e) {
    echo $err("Error: " . htmlspecialchars($e->getMessage()));
}
echo "</div>";

// ─── PASO 4: Test de caché ──────────────────────────────────────────────────
echo "<h3>Paso 4: Sistema de Caché</h3><div class='card'>";
try {
    \Illuminate\Support\Facades\Cache::put('test_cortex', 'ok', now()->addMinutes(1));
    $val = \Illuminate\Support\Facades\Cache::get('test_cortex');
    if ($val === 'ok') {
        echo $ok("Cache funciona correctamente (driver: " . config('cache.default') . ")");
    } else {
        echo $err("Cache put/get falla — la caché no guarda valores");
    }
} catch (\Exception $e) {
    echo $err("Cache error: " . htmlspecialchars($e->getMessage()));
    echo $inf("Solución: usar lógica alternativa sin Cache");
}
echo "</div>";

// ─── PASO 5: Test de envío real ─────────────────────────────────────────────
echo "<h3>Paso 5: Envío Real de Correo (herramienta agotada)</h3><div class='card'>";

if (isset($herramientaPrueba) && $herramientaPrueba) {
    echo $inf("Intentando enviar correo para: <b>{$herramientaPrueba->nombre}</b>...");
    try {
        // Construir trabajadores
        $trabajadores = [];
        if (isset($detalles)) {
            foreach ($detalles as $d) {
                $t = optional($d->vale)->trabajador;
                $trabajadores[] = [
                    'nombre'      => $t ? trim($t->nombre . ' ' . $t->apellidos) : 'Desconocido',
                    'dni'         => optional($t)->dni ?? '—',
                    'cargo'       => optional($t)->cargo ?? '',
                    'telefono'    => optional($t)->telefono ?? '',
                    'vale_codigo' => optional($d->vale)->codigo_vale ?? '—',
                    'fecha_limite'=> optional($d->vale)->fecha_limite
                        ? Carbon::parse($d->vale->fecha_limite)->format('d/m/Y H:i')
                        : '—',
                    'cantidad'    => $d->cantidad_prestada ?? 1,
                ];
            }
        }

        Mail::to('jesusmanuelriveragarcia6@gmail.com')
            ->send(new HerramientaAgotadaMail($herramientaPrueba, $trabajadores, 'agotada'));

        echo "<div style='background:rgba(16,185,129,0.1);border:1px solid #10B981;border-radius:8px;padding:14px;margin-top:12px;'>";
        echo "<b style='color:#10B981;font-size:1.1em;'>✅ CORREO ENVIADO EXITOSAMENTE</b><br>";
        echo "<span style='color:#cfd8e3;'>Revisa tu bandeja de entrada en jesusmanuelriveragarcia6@gmail.com</span>";
        echo "</div>";

    } catch (\Exception $e) {
        echo "<div style='background:rgba(239,68,68,0.1);border:1px solid #ef4444;border-radius:8px;padding:14px;margin-top:12px;'>";
        echo "<b style='color:#ef4444;'>✗ ERROR AL ENVIAR:</b><br>";
        echo "<span style='color:#fca5a5;'>" . htmlspecialchars($e->getMessage()) . "</span><br><br>";
        echo "<b style='color:#64ffda;'>Diagnóstico:</b><br>";
        if (str_contains($e->getMessage(), 'SMTP')) {
            echo "<span style='color:#fcd34d;'>→ Problema de SMTP. Verifica MAIL_PASSWORD en .env (debe ser contraseña de aplicación de Gmail de 16 caracteres)</span>";
        } elseif (str_contains($e->getMessage(), 'class')) {
            echo "<span style='color:#fcd34d;'>→ Clase no encontrada. Ejecuta <b>subir_fix.php</b> primero.</span>";
        } else {
            echo "<span style='color:#fcd34d;'>→ " . htmlspecialchars($e->getMessage()) . "</span>";
        }
        echo "</div>";
    }
} else {
    echo $inf("No hay herramienta agotada/crítica para probar el envío.");
}
echo "</div>";

// ─── PASO 6: Config MAIL ────────────────────────────────────────────────────
echo "<h3>Paso 6: Configuración MAIL en .env</h3><div class='card'>";
echo "<table>";
$configs = ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_FROM_ADDRESS', 'MAIL_SCHEME', 'MAIL_ENCRYPTION'];
foreach ($configs as $cfg) {
    $val = config('mail.' . strtolower(str_replace('MAIL_', '', $cfg)));
    if ($cfg === 'MAIL_MAILER')  $val = config('mail.default');
    if ($cfg === 'MAIL_HOST')    $val = config('mail.mailers.smtp.host');
    if ($cfg === 'MAIL_PORT')    $val = config('mail.mailers.smtp.port');
    if ($cfg === 'MAIL_USERNAME') $val = config('mail.mailers.smtp.username');
    if ($cfg === 'MAIL_FROM_ADDRESS') $val = config('mail.from.address');
    if ($cfg === 'MAIL_SCHEME')  $val = config('mail.mailers.smtp.scheme');
    if ($cfg === 'MAIL_ENCRYPTION') $val = config('mail.mailers.smtp.encryption');
    $display = ($val === null) ? '<span style="color:#ff4444">null/no definido</span>' : htmlspecialchars((string)$val);
    echo "<tr><td>{$cfg}</td><td>{$display}</td></tr>";
}
echo "</table>";
echo "</div>";

echo "<p style='color:#64748b;font-size:12px;margin-top:24px;'>Script de diagnóstico Cortex NOC · " . now()->format('d/m/Y H:i:s') . "</p>";
echo "</body></html>";
