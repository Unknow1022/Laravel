@echo off
chcp 65001 >nul
color 0A
echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║    DEPLOY - Sistema de Gestión de Almacén           ║
echo ║    Sube tu código a GitHub para Railway             ║
echo ╚══════════════════════════════════════════════════════╝
echo.

cd /d "c:\xampp\htdocs\laravel_app"

echo [1/4] Verificando git...
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Git no está instalado. Descárgalo de https://git-scm.com
    pause
    exit /b 1
)
echo ✅ Git OK

echo.
echo [2/4] Preparando archivos...
git add .
echo ✅ Archivos agregados

echo.
echo [3/4] Creando commit...
git commit -m "Sistema completo: modulo sanciones disciplinarias + deploy Railway" 2>&1
echo ✅ Commit listo

echo.
echo [4/4] Subiendo a GitHub...
git push 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ⚠️  No se pudo hacer push automatico.
    echo    Ejecuta manualmente:
    echo.
    echo    git remote add origin https://github.com/TU_USUARIO/sistema-almacen.git
    echo    git push -u origin main
    echo.
) else (
    echo ✅ ¡Código subido a GitHub exitosamente!
)

echo.
echo ════════════════════════════════════════════════════════
echo SIGUIENTE PASO:
echo  1. Ve a https://railway.app
echo  2. New Project → Deploy from GitHub → selecciona tu repo
echo  3. Agrega MySQL como servicio
echo  4. Configura las variables de entorno (ver deploy_guide.md)
echo  5. ¡Listo! Obtendrás tu link público
echo ════════════════════════════════════════════════════════
echo.
pause
