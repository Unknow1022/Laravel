<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HerramientasController;
use App\Http\Controllers\TrabajadoresController;
use App\Http\Controllers\ValesController;
use App\Http\Controllers\IncidenciasController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UsuariosController;

Route::get('/', function () {
    return redirect()->route('login');
});

// ── Documentación Técnica — Diagramas del Sistema (Acceso Público) ──────────
Route::get('/diagramas', function () {
    return response()->file(public_path('diagramas.html'));
})->name('diagramas');

// Consola de Rescate de Cortex (Pública pero segura, restringida a Localhost)
Route::get('/cortex/rescue', [\App\Http\Controllers\CortexRescueController::class, 'index'])->name('cortex.rescue');
Route::post('/cortex/rescue/repair/{component}', [\App\Http\Controllers\CortexRescueController::class, 'repair'])->name('cortex.rescue.repair');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/global-search', [\App\Http\Controllers\SearchController::class, 'global'])->name('api.global-search');

    // Cortex Assistant (Centro de Monitoreo IA)
    Route::get('/cortex', [\App\Http\Controllers\CortexController::class, 'index'])->name('cortex.index');
    Route::get('/cortex/scan', [\App\Http\Controllers\CortexController::class, 'runFullScan'])->name('cortex.scan');
    Route::get('/cortex/export-audit', [\App\Http\Controllers\CortexController::class, 'exportAuditReport'])->name('cortex.export_audit');
    Route::get('/cortex/export-technical', [\App\Http\Controllers\CortexController::class, 'exportTechnicalReport'])->name('cortex.export_technical');
    Route::post('/cortex/repair/{component}', [\App\Http\Controllers\CortexController::class, 'repair'])->name('cortex.repair');
    Route::post('/cortex/incident/dismiss', [\App\Http\Controllers\CortexController::class, 'dismissIncident'])->name('cortex.dismiss');
    Route::post('/cortex/incident/restore', [\App\Http\Controllers\CortexController::class, 'restoreIncidents'])->name('cortex.restore');
    Route::post('/cortex/solve-all', [\App\Http\Controllers\CortexController::class, 'solveAll'])->name('cortex.solve_all');
    Route::post('/cortex/reset-all', [\App\Http\Controllers\CortexController::class, 'resetAll'])->name('cortex.reset_all');
    Route::post('/cortex/ask', [\App\Http\Controllers\CortexController::class, 'askAssistant'])->name('cortex.ask');
    Route::post('/cortex/test-mail', [\App\Http\Controllers\CortexController::class, 'testMail'])->name('cortex.test_mail');
    
    // Cortex Security Sandbox - Exposición de vulnerabilidades en vivo
    Route::post('/cortex/demo/toggle-xss', [\App\Http\Controllers\SecurityDemoController::class, 'toggleXss'])->name('cortex.demo.toggle_xss');
    Route::post('/cortex/demo/toggle-sqli', [\App\Http\Controllers\SecurityDemoController::class, 'toggleSqli'])->name('cortex.demo.toggle_sqli');

    // ── RUTAS DE LECTURA (Administrador, Almacenero, Supervisor) ─────────────────
    // Herramientas
    Route::get('herramientas', [HerramientasController::class, 'index'])->name('herramientas.index');
    Route::get('herramientas/ubicaciones', [HerramientasController::class, 'ubicaciones'])->name('herramientas.ubicaciones');

    // Almacenes & Categorías
    Route::get('almacenes', [\App\Http\Controllers\AlmacenesController::class, 'index'])->name('almacenes.index');
    Route::get('almacenes/{almacene}/categorias', [\App\Http\Controllers\CategoriasController::class, 'index'])->name('almacenes.categorias.index');

    // Trabajadores
    Route::get('trabajadores', [TrabajadoresController::class, 'index'])->name('trabajadores.index');
    Route::get('trabajadores/inactivos', [TrabajadoresController::class, 'inactivos'])->name('trabajadores.inactivos');

    // Vales
    Route::get('vales', [ValesController::class, 'index'])->name('vales.index');
    Route::get('vales/historial', [ValesController::class, 'historial'])->name('vales.historial');

    // Incidencias & Mantenimiento (Lectura/Vista)
    Route::get('incidencias', [IncidenciasController::class, 'index'])->name('incidencias.index');
    Route::get('mantenimiento', [MantenimientoController::class, 'index'])->name('mantenimiento.index');


    // ── RUTAS OPERATIVAS (Administrador, Almacenero) ───────────────────────────
    Route::middleware(['role:Administrador,Almacenero'])->group(function () {
        // Herramientas
        Route::get('herramientas/create', [HerramientasController::class, 'create'])->name('herramientas.create');
        Route::post('herramientas', [HerramientasController::class, 'store'])->name('herramientas.store');
        Route::get('herramientas/{herramienta}/edit', [HerramientasController::class, 'edit'])->name('herramientas.edit');
        Route::put('herramientas/{herramienta}', [HerramientasController::class, 'update'])->name('herramientas.update');
        Route::post('herramientas/{id}/restock', [HerramientasController::class, 'restock'])->name('herramientas.restock');

        // Categorías
        Route::post('almacenes/{almacene}/categorias', [\App\Http\Controllers\CategoriasController::class, 'store'])->name('almacenes.categorias.store');
        Route::put('almacenes/{almacene}/categorias/{categoria}', [\App\Http\Controllers\CategoriasController::class, 'update'])->name('almacenes.categorias.update');

        // Trabajadores
        Route::get('trabajadores/create', [TrabajadoresController::class, 'create'])->name('trabajadores.create');
        Route::post('trabajadores', [TrabajadoresController::class, 'store'])->name('trabajadores.store');
        Route::get('trabajadores/{trabajadore}/edit', [TrabajadoresController::class, 'edit'])->name('trabajadores.edit');
        Route::put('trabajadores/{trabajadore}', [TrabajadoresController::class, 'update'])->name('trabajadores.update');

        // Vales
        Route::get('vales/devolver', [ValesController::class, 'devolver'])->name('vales.devolver_form');
        Route::post('vales/buscar', [ValesController::class, 'buscarVale'])->name('vales.buscar');
        Route::get('vales/procesar_devolucion/{vale}', [ValesController::class, 'procesar_devolucion'])->name('vales.procesar_devolucion');
        Route::post('vales/procesar_devolucion/{vale}', [ValesController::class, 'guardar_devolucion'])->name('vales.guardar_devolucion');
        Route::get('vales/create', [ValesController::class, 'create'])->name('vales.create');
        Route::post('vales', [ValesController::class, 'store'])->name('vales.store');

        // Incidencias & Mantenimiento (Acción/Escritura)
        Route::post('incidencias', [IncidenciasController::class, 'store'])->name('incidencias.store');
        Route::post('incidencias/{id}/resolver', [IncidenciasController::class, 'resolver'])->name('incidencias.resolver');
        Route::post('mantenimiento/{id}/reparar', [MantenimientoController::class, 'reparar'])->name('mantenimiento.reparar');

        // Baja de trabajadores (Administrador y Almacenero)
        Route::delete('trabajadores/{trabajadore}', [TrabajadoresController::class, 'destroy'])->name('trabajadores.destroy');
    });


    // ── RUTAS EXCLUSIVAS DE REPORTES (Administrador, Supervisor) ─────────────────
    Route::middleware(['role:Administrador,Supervisor'])->group(function () {
        Route::get('reportes', [ReportesController::class, 'index'])->name('reportes.index');
        Route::get('reportes/herramientas', [ReportesController::class, 'herramientas'])->name('reportes.herramientas');
        Route::get('reportes/usuarios', [ReportesController::class, 'usuarios'])->name('reportes.usuarios');
        Route::get('reportes/personal', [ReportesController::class, 'personal'])->name('reportes.personal');
        Route::get('reportes/temporal', [ReportesController::class, 'temporal'])->name('reportes.temporal');
        Route::get('reportes/bug-export', [ReportesController::class, 'exportBugWord'])->name('reportes.bug_export');
        Route::get('reportes/matriz-dispositivos-export', [ReportesController::class, 'exportMatrizWord'])->name('reportes.matriz_export');
    });


    // ── RUTAS DE LECTURA DE DETALLE (Wildcards - Colocadas abajo para evitar colisiones con estáticas) ──
    Route::get('herramientas/{herramienta}', [HerramientasController::class, 'show'])->name('herramientas.show');
    Route::get('trabajadores/{trabajadore}', [TrabajadoresController::class, 'show'])->name('trabajadores.show')->withTrashed();
    Route::get('vales/{vale}', [ValesController::class, 'show'])->name('vales.show');


    // ── RUTAS EXCLUSIVAS DE ADMINISTRADOR ───────────────────────────────────────
    Route::middleware(['role:Administrador'])->group(function () {
        // Gestión de Usuarios
        Route::resource('usuarios', UsuariosController::class);

        // Eliminación física/lógica de recursos críticos
        Route::delete('herramientas/{herramienta}', [HerramientasController::class, 'destroy'])->name('herramientas.destroy');
        Route::post('trabajadores/{id}/reactivar', [TrabajadoresController::class, 'reactivar'])->name('trabajadores.reactivar');

        // Almacenes (creación, edición y eliminación de almacenes físicos)
        Route::get('almacenes/create', [\App\Http\Controllers\AlmacenesController::class, 'create'])->name('almacenes.create');
        Route::post('almacenes', [\App\Http\Controllers\AlmacenesController::class, 'store'])->name('almacenes.store');
        Route::get('almacenes/{almacene}/edit', [\App\Http\Controllers\AlmacenesController::class, 'edit'])->name('almacenes.edit');
        Route::put('almacenes/{almacene}', [\App\Http\Controllers\AlmacenesController::class, 'update'])->name('almacenes.update');
        Route::delete('almacenes/{almacene}', [\App\Http\Controllers\AlmacenesController::class, 'destroy'])->name('almacenes.destroy');

        // Categorías (eliminación)
        Route::delete('almacenes/{almacene}/categorias/{categoria}', [\App\Http\Controllers\CategoriasController::class, 'destroy'])->name('almacenes.categorias.destroy');
    });
});

require __DIR__.'/auth.php';
