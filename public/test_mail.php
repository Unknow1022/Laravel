<?php
// Seguridad: solo desde localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    http_response_code(403);
    die('Acceso denegado.');
}

require __DIR__ . '/../bootstrap/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
<style>
  body { background: #050D1A; font-family: monospace; padding: 30px; color: #CCD6F6; }
  .box { background: #0A192F; border: 1px solid #1E3A5F; border-radius: 12px; padding: 30px; max-width: 600px; margin: 0 auto; }
  h2 { color: #64FFDA; }
  .ok  { color: #10B981; font-size: 18px; }
  .err { color: #EF4444; font-size: 18px; }
  pre  { background: #112240; padding: 16px; border-radius: 8px; white-space: pre-wrap; color: #8892B0; font-size: 13px; }
  .cfg { color: #64FFDA; } .val { color: #E6F1FF; }
</style></head><body><div class='box'>";

echo "<h2>📧 Test de Correo — Almacén Inteligente</h2>";

// Mostrar configuración actual (sin mostrar la contraseña completa)
$pass = config('mail.mailers.smtp.password');
$passMasked = $pass ? substr($pass, 0, 4) . str_repeat('*', max(0, strlen($pass) - 4)) : '(vacío)';

echo "<pre>";
echo "<span class='cfg'>MAIL_MAILER</span>   = <span class='val'>" . config('mail.default') . "</span>\n";
echo "<span class='cfg'>MAIL_HOST</span>     = <span class='val'>" . config('mail.mailers.smtp.host') . "</span>\n";
echo "<span class='cfg'>MAIL_PORT</span>     = <span class='val'>" . config('mail.mailers.smtp.port') . "</span>\n";
echo "<span class='cfg'>MAIL_USERNAME</span> = <span class='val'>" . config('mail.mailers.smtp.username') . "</span>\n";
echo "<span class='cfg'>MAIL_PASSWORD</span> = <span class='val'>" . $passMasked . "</span>\n";
echo "<span class='cfg'>MAIL_FROM</span>     = <span class='val'>" . config('mail.from.address') . "</span>\n";
echo "</pre>";

echo "<hr style='border-color:#1E3A5F; margin: 20px 0;'>";
echo "<p>Enviando correo de prueba a <strong style='color:#64FFDA;'>jesusmanuelriveragarcia6@gmail.com</strong>...</p>";

try {
    Illuminate\Support\Facades\Mail::raw(
        "✅ ¡Correo de prueba exitoso!\n\nEl sistema Almacén Inteligente puede enviar correos correctamente.\nFecha: " . now()->format('d/m/Y H:i:s'),
        function ($message) {
            $message->to('jesusmanuelriveragarcia6@gmail.com')
                    ->subject('✅ Prueba de correo — Almacén Inteligente');
        }
    );

    echo "<p class='ok'>✅ ¡Correo enviado exitosamente! Revisa tu bandeja de entrada (y spam).</p>";

} catch (\Exception $e) {
    echo "<p class='err'>❌ Error al enviar el correo:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";

    // Diagnóstico de errores comunes
    echo "<p style='color:#F59E0B; margin-top:16px;'><strong>Posibles causas:</strong></p><pre>";
    if (str_contains($e->getMessage(), 'Authentication')) {
        echo "🔑 La contraseña de aplicación es incorrecta o está mal escrita.\n";
        echo "   → Genera una nueva en: https://myaccount.google.com/apppasswords\n";
        echo "   → Pégala en .env sin espacios, entre comillas: MAIL_PASSWORD=\"abcdwxyz1234efgh\"\n";
    } elseif (str_contains($e->getMessage(), 'Connection')) {
        echo "🌐 No se pudo conectar a smtp.gmail.com:587\n";
        echo "   → Verifica que XAMPP tenga acceso a Internet.\n";
        echo "   → Firewall/antivirus puede estar bloqueando el puerto 587.\n";
    } elseif (str_contains($e->getMessage(), 'stream')) {
        echo "🔒 Error de cifrado SSL/TLS.\n";
        echo "   → Verifica que MAIL_SCHEME=tls en tu .env\n";
    } else {
        echo "ℹ️  Revisa el log de Laravel: storage/logs/laravel.log\n";
    }
    echo "</pre>";
}

echo "<hr style='border-color:#1E3A5F; margin: 20px 0;'>";
echo "<p style='color:#4A5568; font-size:12px;'>⚠️  Elimina este archivo después de usarlo: <code>public/test_mail.php</code></p>";
echo "</div></body></html>";
