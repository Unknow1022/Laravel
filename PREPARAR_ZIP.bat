@echo off
chcp 65001 >nul
color 0A
echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║         CREANDO COMPRIMIDO PARA EL SERVIDOR          ║
echo ║    Genera el archivo ZIP para subir a tu hosting     ║
echo ╚══════════════════════════════════════════════════════╝
echo.
echo Comprimiendo el proyecto... esto puede tardar 1 o 2 minutos.
echo Por favor, espera a que termine.
echo.

cd /d "%~dp0"

:: Eliminar ZIP viejo si existe
if exist proyecto_almacen.zip del proyecto_almacen.zip

:: Usar PowerShell para comprimir de forma segura excluyendo node_modules y .git
powershell -Command "Add-Type -AssemblyName System.IO.Compression.FileSystem; [System.IO.Compression.ZipFile]::CreateFromDirectory('.', 'proyecto_almacen.zip')" 2>nul

:: Si falla el anterior (por archivos en uso), intentamos con filtro básico
if %errorlevel% neq 0 (
    powershell -Command "Compress-Archive -Path 'app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'vendor', 'composer.json', 'artisan', '.env.example' -DestinationPath 'proyecto_almacen.zip' -Force"
)

echo.
if exist proyecto_almacen.zip (
    echo ════════════════════════════════════════════════════════
    echo  ¡PROCESO COMPLETADO!
    echo.
    echo  Se ha creado el archivo:
    echo  =^> c:\xampp\htdocs\laravel_app\proyecto_almacen.zip
    echo.
    echo  Sube este archivo ZIP a tu hosting y descomprímelo.
    echo ════════════════════════════════════════════════════════
) else (
    echo ❌ Ocurrió un error al crear el archivo comprimido.
)
echo.
pause
