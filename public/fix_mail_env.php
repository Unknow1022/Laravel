<?php
/**
 * Actualiza las variables MAIL_* en el .env de producción (InfinityFree).
 * Abre: https://almacen-inteligente.infinityfreeapp.com/public/fix_mail_env.php
 */

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request  = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$kernel->terminate($request, $response);

header('Content-Type: text/html; charset=utf-8');

$envPath = __DIR__ . '/../.env';

// ─── Nuevos valores de MAIL para Gmail SMTP ───────────────────────────────
$mailConfig = [
    'MAIL_MAILER'       => 'smtp',
    'MAIL_SCHEME'       => 'null',
    'MAIL_HOST'         => 'smtp.gmail.com',
    'MAIL_PORT'         => '587',
    'MAIL_USERNAME'     => 'jesusmanuelriveragarcia6@gmail.com',
    'MAIL_PASSWORD'     => '"wtcuftsvpoortrmu"',
    'MAIL_FROM_ADDRESS' => '"jesusmanuelriveragarcia6@gmail.com"',
    'MAIL_FROM_NAME'    => '"Almacen Inteligente"',
];

$result  = '';
$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!file_exists($envPath)) {
        $error = "No se encontró el archivo .env en: $envPath";
    } else {
        $content = file_get_contents($envPath);

        foreach ($mailConfig as $key => $value) {
            // Si la clave existe, reemplaza su valor
            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
                $result .= "✓ Actualizado: {$key}={$value}\n";
            } else {
                // Si no existe, la añade al final
                $content .= "\n{$key}={$value}";
                $result .= "✓ Añadido: {$key}={$value}\n";
            }
        }

        if (file_put_contents($envPath, $content) !== false) {
            $success = true;
            // Limpiar caché de configuración si existe
            $cacheFile = __DIR__ . '/../bootstrap/cache/config.php';
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
                $result .= "✓ Caché de config eliminada\n";
            }
        } else {
            $error = "No se pudo escribir en el archivo .env (permisos insuficientes)";
        }
    }
}

// Leer .env actual para mostrar
$currentEnv = file_exists($envPath) ? file_get_contents($envPath) : 'No encontrado';
preg_match_all('/^MAIL_.*/m', $currentEnv, $matches);
$currentMailLines = implode("\n", $matches[0]);

?><!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Fix Mail .env</title>
<style>
  body{background:#070f1a;color:#cfd8e3;font-family:monospace;padding:24px;font-size:14px;line-height:1.7}
  h2{color:#64ffda}h3{color:#e6f1ff;border-bottom:1px solid #1e293b;padding-bottom:6px}
  .card{background:#0f172a;border:1px solid #1e293b;border-radius:10px;padding:18px;margin-bottom:18px}
  .ok{color:#00ff88;font-weight:bold}.err{color:#ff4444;font-weight:bold}
  pre{background:#030712;padding:14px;border-radius:8px;border:1px solid #1e293b;white-space:pre-wrap}
  .btn{background:#64ffda;color:#030712;font-weight:900;font-size:1rem;padding:14px 32px;
       border:none;border-radius:8px;cursor:pointer;letter-spacing:1px;margin-top:12px;
       text-transform:uppercase;width:100%}
  .btn:hover{background:#00ffcc}
  table{border-collapse:collapse;width:100%}
  td,th{padding:8px 12px;border:1px solid #1e293b;font-size:13px}
  th{background:#112240;color:#64ffda}td:first-child{color:#94a3b8;width:40%}
  .new{color:#64ffda}
</style></head><body>

<h2>⚙️ Fix MAIL .env — Producción InfinityFree</h2>

<?php if ($error): ?>
<div class='card' style='border-color:#ef4444'><span class='err'>✗ Error: <?= htmlspecialchars($error) ?></span></div>
<?php endif; ?>

<?php if ($success): ?>
<div class='card' style='border-color:#10B981;background:rgba(16,185,129,0.06)'>
    <b style='color:#10B981;font-size:1.1em'>✅ .env actualizado correctamente</b><br><br>
    <pre class='ok'><?= htmlspecialchars($result) ?></pre>
    <br>
    <b style='color:#e6f1ff'>Siguiente paso:</b> envía un correo de prueba para confirmar que ahora llega a Gmail:<br><br>
    <a href='/public/test_stock_mail.php'
       style='color:#64ffda;font-weight:700;font-size:1.05em;text-decoration:none;
              border:1px solid #64ffda;padding:10px 20px;border-radius:6px;display:inline-block;margin-top:8px'>
       🧪 Abrir diagnóstico y probar envío →
    </a>
</div>
<?php else: ?>

<div class='card'>
    <h3>📋 Configuración actual en el .env remoto</h3>
    <pre><?= htmlspecialchars($currentMailLines ?: 'Sin variables MAIL_ definidas (usando defaults)') ?></pre>
</div>

<div class='card'>
    <h3>🔄 Cambios que se aplicarán</h3>
    <table>
        <tr><th>Variable</th><th>Valor nuevo</th></tr>
        <?php foreach ($mailConfig as $k => $v): ?>
        <tr>
            <td><?= $k ?></td>
            <td class='new'><?= htmlspecialchars($v) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <form method="POST">
        <button type="submit" class="btn">🚀 Aplicar configuración Gmail SMTP ahora</button>
    </form>
</div>

<?php endif; ?>

<p style='color:#64748b;font-size:12px;margin-top:28px'>
    Cortex NOC — fix_mail_env.php · <?= date('d/m/Y H:i:s') ?>
</p>
</body></html>
