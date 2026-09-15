@echo off
chcp 65001 >nul
color 0B
echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║         COMPARTIR SISTEMA A INTERNET                 ║
echo ║    Genera un enlace seguro para tu profesor          ║
echo ╚══════════════════════════════════════════════════════╝
echo.
echo Este script creará un enlace HTTPS público temporal para que tu 
echo profesor pueda acceder a tu sistema local desde cualquier lugar
echo y realizar sus pruebas de vulnerabilidad de forma segura.
echo.
echo [!] IMPORTANTE: Tu servidor local (php artisan serve) debe estar
echo     activo en el puerto 8000 antes de continuar.
echo.
pause

echo.
echo [1/2] Verificando conexión segura con SSH de Windows...
where ssh >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ❌ SSH no está habilitado en tu Windows.
    echo.
    echo Para solucionarlo rápido:
    echo 1. Abre PowerShell como Administrador.
    echo 2. Ejecuta: Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0
    echo 3. Vuelve a ejecutar este archivo.
    echo.
    pause
    exit /b 1
)
echo ✅ SSH detectado.

echo.
echo [2/2] Creando túnel seguro a http://127.0.0.1:8000...
echo ────────────────────────────────────────────────────────
echo El sistema generará una dirección que termina en ".pinggy.link"
echo Copia la dirección que empieza con "https://" y mándasela al profesor.
echo NO cierres esta ventana mientras el profesor esté revisando.
echo ────────────────────────────────────────────────────────
echo.

ssh -t -o StrictHostKeyChecking=no -R 80:127.0.0.1:8000 a.pinggy.io

echo.
echo Tunnel cerrado.
pause
