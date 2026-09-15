<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documentación Técnica — Sistema Almacén Inteligente</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #fff;
            padding: 20px;
            line-height: 1.6;
        }

        /* ── PORTADA ── */
        .cover {
            text-align: center;
            padding: 60px 30px;
            border-bottom: 3px solid #0056b3;
            margin-bottom: 30px;
        }
        .cover .badge-cortex {
            display: inline-block;
            background: #0056b3;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 4px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            margin-bottom: 18px;
        }
        .cover h1 {
            font-size: 26px;
            color: #0056b3;
            font-weight: 900;
            margin-bottom: 8px;
            line-height: 1.2;
        }
        .cover h2 {
            font-size: 15px;
            color: #444;
            font-weight: 400;
            margin-bottom: 20px;
            border: none;
            padding: 0;
        }
        .cover .meta-grid {
            display: table;
            width: 100%;
            margin-top: 20px;
            border: 1px solid #dce3ef;
            border-radius: 6px;
            overflow: hidden;
        }
        .cover .meta-row { display: table-row; }
        .cover .meta-cell {
            display: table-cell;
            padding: 8px 14px;
            font-size: 11px;
            border-bottom: 1px solid #e8ecf4;
        }
        .cover .meta-label { color: #666; width: 35%; }
        .cover .meta-value { color: #0056b3; font-weight: bold; }

        /* ── TIPOGRAFÍA ── */
        h2 {
            font-size: 15px;
            color: #0056b3;
            border-left: 4px solid #0056b3;
            padding-left: 10px;
            margin-top: 28px;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 12.5px;
            color: #1a1a2e;
            margin-top: 16px;
            margin-bottom: 6px;
            font-weight: bold;
        }
        p { margin-bottom: 8px; color: #333; }

        /* ── SECCIONES ── */
        .section { margin-bottom: 20px; }
        .section-title {
            background: #f0f4ff;
            border-left: 5px solid #0056b3;
            padding: 8px 14px;
            font-size: 14px;
            font-weight: bold;
            color: #0056b3;
            margin-top: 30px;
            margin-bottom: 14px;
        }

        /* ── CÓDIGO ── */
        .code-block {
            background: #1a1a2e;
            color: #a8d8ea;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            padding: 12px 14px;
            border-radius: 4px;
            margin: 8px 0 14px 0;
            line-height: 1.5;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .code-comment { color: #64FFDA; }
        .code-keyword { color: #F97316; }
        .code-string  { color: #A3E635; }

        .inline-code {
            background: #eef2ff;
            color: #4338ca;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            padding: 1px 5px;
            border-radius: 3px;
        }

        /* ── TABLAS ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 16px 0;
            font-size: 11px;
        }
        th {
            background: #0056b3;
            color: #fff;
            padding: 7px 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        tr:nth-child(even) td { background: #f8faff; }
        tr:hover td { background: #eef2ff; }
        td strong { color: #0056b3; }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-validated { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-cp { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .badge-mp { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .badge-lp { background: #ede9fe; color: #5b21b6; border: 1px solid #c4b5fd; }

        /* ── CALLOUT / INFO BOXES ── */
        .callout {
            border-left: 4px solid #3b82f6;
            background: #eff6ff;
            padding: 10px 14px;
            margin: 10px 0;
            border-radius: 0 6px 6px 0;
        }
        .callout.warning { border-color: #f59e0b; background: #fffbeb; }
        .callout.success { border-color: #10b981; background: #ecfdf5; }
        .callout p { margin: 0; font-size: 11px; }
        .callout strong { color: #1e40af; }
        .callout.warning strong { color: #92400e; }
        .callout.success strong { color: #065f46; }

        /* ── ÁRBOL DE DIRECTORIOS ── */
        .dir-tree {
            background: #0f172a;
            color: #94a3b8;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            padding: 14px 18px;
            border-radius: 6px;
            margin: 8px 0 14px 0;
            line-height: 1.8;
        }
        .dir-tree .dir  { color: #60a5fa; }
        .dir-tree .file { color: #a8d8ea; }
        .dir-tree .note { color: #64FFDA; font-style: italic; }

        /* ── LISTA DE PASOS ── */
        .steps-list { margin: 8px 0; padding-left: 0; list-style: none; }
        .steps-list li {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .step-num {
            display: table-cell;
            width: 28px;
            background: #0056b3;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            border-radius: 50%;
            vertical-align: middle;
            padding: 4px;
        }
        .step-text {
            display: table-cell;
            padding-left: 10px;
            vertical-align: middle;
            font-size: 11px;
        }

        /* ── LECCIONES ── */
        .lesson-card {
            border: 1px solid #e2e8f0;
            border-left: 4px solid #6366f1;
            padding: 10px 14px;
            margin-bottom: 8px;
            border-radius: 0 6px 6px 0;
        }
        .lesson-card .num { color: #6366f1; font-weight: bold; margin-right: 5px; }
        .lesson-card .title { font-weight: bold; color: #1a1a2e; }
        .lesson-card .body { color: #555; font-size: 11px; margin-top: 3px; }

        /* ── HIPÓTESIS ── */
        .hypothesis-box {
            border: 2px solid #10b981;
            background: #ecfdf5;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 10px 0;
        }
        .hypothesis-box .label {
            font-size: 10px;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .hypothesis-box blockquote {
            font-style: italic;
            color: #065f46;
            font-size: 11px;
            border-left: 3px solid #10b981;
            padding-left: 10px;
            margin: 6px 0;
        }

        /* ── PIE DE PÁGINA ── */
        .footer {
            margin-top: 50px;
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
        .footer .footer-brand { color: #0056b3; font-weight: bold; }

        /* ── SEPARADORES ── */
        hr {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 20px 0;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    <!-- ══════════════════════════════════════════════════════════════
         PORTADA
    ══════════════════════════════════════════════════════════════ -->
    <div class="cover">
        <div class="badge-cortex">Cortex NOC — Documentación Técnica</div>
        <h1>Sistema Almacén Inteligente</h1>
        <h2>Secciones 3.8 y 3.9 — Implementación Técnica y Conclusiones</h2>
        <div class="meta-grid">
            <div class="meta-row">
                <div class="meta-cell meta-label">Generado por:</div>
                <div class="meta-cell meta-value">{{ $generated_by }}</div>
                <div class="meta-cell meta-label">Fecha de emisión:</div>
                <div class="meta-cell meta-value">{{ $date }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-cell meta-label">Framework:</div>
                <div class="meta-cell meta-value">Laravel {{ $laravel_version }}</div>
                <div class="meta-cell meta-label">PHP Runtime:</div>
                <div class="meta-cell meta-value">{{ $php_version }}</div>
            </div>
            <div class="meta-row">
                <div class="meta-cell meta-label">Entorno:</div>
                <div class="meta-cell meta-value">{{ strtoupper($environment) }}</div>
                <div class="meta-cell meta-label">Suite de Pruebas:</div>
                <div class="meta-cell meta-value">{{ $total_tests }} Tests — PHPUnit 11.5.55</div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         3.8 IMPLEMENTACIÓN TÉCNICA
    ══════════════════════════════════════════════════════════════ -->
    <div class="section-title">3.8 — Implementación Técnica</div>

    <!-- 3.8.1 Arquitectura -->
    <h2>3.8.1 Arquitectura del Módulo Cortex NOC</h2>
    <p>
        El sistema <strong>Cortex NOC (Neural Operations Center)</strong> sigue el patrón
        arquitectónico <strong>MVC</strong> propio de Laravel 12, extendido con una capa de
        <strong>Servicios</strong> especializados que encapsulan la lógica de negocio compleja
        separándola de los controladores, facilitando las pruebas y el mantenimiento.
    </p>

    <div class="dir-tree">
<span class="dir">app/</span>
├── <span class="dir">Http/Controllers/</span>
│   └── <span class="file">CortexController.php</span>      <span class="note"># Controlador principal del NOC</span>
├── <span class="dir">Services/</span>
│   ├── <span class="file">AIService.php</span>             <span class="note"># Análisis de anomalías y predicciones</span>
│   ├── <span class="file">CortexAuditorService.php</span>  <span class="note"># Registro de eventos de auditoría en BD</span>
│   ├── <span class="file">CortexAutomationService.php</span> <span class="note"># Reparaciones automatizadas (BD, caché, permisos)</span>
│   ├── <span class="file">CortexSecurityService.php</span> <span class="note"># Escaneo de seguridad activa</span>
│   └── <span class="file">SystemValidatorService.php</span> <span class="note"># Diagnóstico de componentes del sistema</span>
├── <span class="dir">Models/</span>
│   ├── <span class="file">Herramienta.php</span>           <span class="note"># Activos del almacén (soft-delete)</span>
│   ├── <span class="file">Vale.php</span>                  <span class="note"># Préstamos y devoluciones</span>
│   ├── <span class="file">Trabajador.php</span>            <span class="note"># Personal del almacén</span>
│   ├── <span class="file">Usuario.php</span>               <span class="note"># Usuarios del sistema (admin / almacenero)</span>
│   └── <span class="file">Almacen.php</span>               <span class="note"># Almacenes físicos</span>
<span class="dir">resources/views/cortex/</span>
│   ├── <span class="file">index.blade.php</span>           <span class="note"># Panel NOC (4 tabs: Monitoreo, Seguridad, Diagnóstico, Inteligencia)</span>
│   ├── <span class="file">report.blade.php</span>          <span class="note"># Plantilla HTML/CSS del informe de auditoría PDF</span>
│   └── <span class="file">technical_report.blade.php</span> <span class="note"># Este documento — Documentación técnica PDF</span>
<span class="dir">database/migrations/</span>
│   └── <span class="file">2026_05_14_..._create_cortex_events_table.php</span> <span class="note"># Tabla de auditoría</span>
<span class="dir">tests/Feature/</span>
│   └── <span class="file">CortexAutomationTest.php</span>  <span class="note"># 5 tests de integración del módulo Cortex</span></div>

    <!-- 3.8.2 Código Comentado -->
    <h2>3.8.2 Código Fuente Comentado — Módulos Principales</h2>

    <h3>SystemValidatorService.php — Diagnóstico del Sistema</h3>
    <p>Servicio que ejecuta una batería de 4 pruebas de salud. Cada prueba retorna
    <span class="inline-code">['status' =&gt; 'PASS|FAIL', 'message' =&gt; '...']</span>.</p>
    <div class="code-block"><span class="code-comment">/**
 * Batería de pruebas de salud del sistema.
 * Se ejecuta al iniciar un escaneo (runFullScan) y al exportar el reporte de auditoría.
 */</span>
<span class="code-keyword">public function</span> runFullDiagnostic(): <span class="code-keyword">array</span>
{
    <span class="code-keyword">return</span> [
        <span class="code-string">'database'</span> => $this->checkDatabaseHealth(),    <span class="code-comment">// Conectividad y tablas críticas</span>
        <span class="code-string">'models'</span>   => $this->checkModelIntegrity(),    <span class="code-comment">// Relaciones ORM (Vale → Trabajador)</span>
        <span class="code-string">'storage'</span>  => $this->checkStoragePermissions(), <span class="code-comment">// is_writable(storage_path('logs'))</span>
        <span class="code-string">'security'</span> => $this->checkSecurityVulnerabilities() <span class="code-comment">// APP_DEBUG, APP_KEY</span>
    ];
}</div>

    <h3>CortexAuditorService.php — Registro de Eventos Auditados</h3>
    <p>Cada acción relevante del sistema queda registrada en la tabla
    <span class="inline-code">cortex_events</span> con tipo, severidad y metadatos JSON.</p>
    <div class="code-block"><span class="code-comment">/**
 * @param string $type      SECURITY | DIAGNOSTIC | SYSTEM | TRANSACTION
 * @param string $message   Descripción legible del evento
 * @param string $severity  LOW | MEDIUM | HIGH | CRITICAL
 * @param array  $metadata  Datos técnicos adicionales (se serializa en JSON)
 * @param bool   $authorized ¿Fue autorizado por un Administrador?
 */</span>
<span class="code-keyword">public static function</span> log(
    <span class="code-keyword">string</span> $type, <span class="code-keyword">string</span> $message,
    <span class="code-keyword">string</span> $severity = <span class="code-string">'INFO'</span>,
    <span class="code-keyword">array</span> $metadata = [],
    <span class="code-keyword">bool</span> $authorized = <span class="code-keyword">false</span>
): <span class="code-keyword">void</span> {
    DB::table(<span class="code-string">'cortex_events'</span>)->insert([
        <span class="code-string">'type'</span>          => $type,
        <span class="code-string">'severity'</span>      => $severity,
        <span class="code-string">'message'</span>       => $message,
        <span class="code-string">'metadata'</span>      => json_encode($metadata), <span class="code-comment">// Metadatos técnicos en JSON</span>
        <span class="code-string">'user_id'</span>       => Auth::id(),             <span class="code-comment">// Usuario autenticado</span>
        <span class="code-string">'is_authorized'</span> => $authorized,
        <span class="code-string">'created_at'</span>    => now()
    ]);
}</div>

    <h3>CortexController.php — Flujo de Escaneo y Reporte Automático</h3>
    <div class="code-block"><span class="code-comment">/**
 * 1. Mide el tiempo de ejecución del diagnóstico.
 * 2. Registra el evento en cortex_events via CortexAuditorService.
 * 3. Redirige al NOC con la sesión 'scan_completed' activa.
 * 4. El frontend detecta la sesión y descarga el PDF automáticamente.
 */</span>
<span class="code-keyword">public function</span> runFullScan(): RedirectResponse
{
    $startTime = microtime(<span class="code-keyword">true</span>);                 <span class="code-comment">// Marca de inicio</span>
    $results   = $validator->runFullDiagnostic();  <span class="code-comment">// Ejecuta 4 pruebas</span>
    $scanTime  = round((microtime(<span class="code-keyword">true</span>) - $startTime) * 1000, 2) . <span class="code-string">' ms'</span>;

    $hasErrors = <span class="code-keyword">false</span>; $summary = [];
    <span class="code-keyword">foreach</span> ($results <span class="code-keyword">as</span> $component => $res) {
        <span class="code-keyword">if</span> ($res[<span class="code-string">'status'</span>] === <span class="code-string">'FAIL'</span>) $hasErrors = <span class="code-keyword">true</span>;
        $summary[] = ucfirst($component) . <span class="code-string">': '</span> . $res[<span class="code-string">'status'</span>];
    }

    CortexAuditorService::log(              <span class="code-comment">// Registrar en BD</span>
        <span class="code-string">'DIAGNOSTIC'</span>,
        <span class="code-string">'Escaneo completo finalizado. '</span> . ($hasErrors ? <span class="code-string">'Anomalías detectadas.'</span> : <span class="code-string">'Sistema nominal.'</span>),
        $hasErrors ? <span class="code-string">'HIGH'</span> : <span class="code-string">'LOW'</span>,
        [<span class="code-string">'results'</span> => $summary]
    );

    <span class="code-keyword">return</span> redirect()->route(<span class="code-string">'cortex.index'</span>)
        ->with([<span class="code-string">'scan_completed'</span> => <span class="code-keyword">true</span>, <span class="code-string">'scan_errors'</span> => $hasErrors]);
}</div>

    <h3>CortexAutomationService.php — Protocolos de Reparación Automatizada</h3>
    <p>Implementa 3 protocolos reales de reparación, ejecutables solo por Administradores:</p>
    <table>
        <thead><tr><th>Protocolo</th><th>Acción Real</th><th>Comando / Función</th></tr></thead>
        <tbody>
            <tr><td><span class="inline-code">purge_cache</span></td><td>Limpia caché de Laravel</td><td><span class="inline-code">Artisan::call('cache:clear')</span> + <span class="inline-code">view:clear</span> + <span class="inline-code">config:clear</span></td></tr>
            <tr><td><span class="inline-code">optimize_db</span></td><td>Desfragmenta índices en MySQL</td><td><span class="inline-code">OPTIMIZE TABLE herramientas, vales, ...</span></td></tr>
            <tr><td><span class="inline-code">fix_permissions</span></td><td>Restaura permisos en <span class="inline-code">storage/</span></td><td><span class="inline-code">chmod($path, 0775)</span> recursivo en 8 directorios</td></tr>
        </tbody>
    </table>

    <h3>Frontend — Descarga Automática del Reporte (Blade + JavaScript)</h3>
    <div class="code-block">@verbatim<span class="code-comment">// Al detectar sesión 'scan_completed', muestra el SweetAlert y
// crea dinámicamente un enlace de descarga para disparar el PDF.</span>
@if(session(<span class="code-string">'scan_completed'</span>))
    Swal.fire({ title: <span class="code-string">'...'</span>, text: <span class="code-string">'...Reporte PDF generado e iniciado...'</span> });

    setTimeout(() => {
        <span class="code-keyword">const</span> link = document.createElement(<span class="code-string">'a'</span>);
        link.href = <span class="code-string">"{{ route('cortex.export_audit') }}"</span>;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }, 500);
@endif@endverbatim</div>

    <!-- 3.8.3 Dependencias -->
    <div class="page-break"></div>
    <h2>3.8.3 Librerías y Dependencias Utilizadas</h2>
    <table>
        <thead>
            <tr><th style="width:28%;">Librería / Herramienta</th><th style="width:15%;">Versión</th><th>Rol en el Proyecto</th></tr>
        </thead>
        <tbody>
            <tr><td><strong>Laravel Framework</strong></td><td>12.58.0</td><td>Framework MVC principal. Routing, ORM (Eloquent), autenticación, Artisan, Blade, colas y eventos.</td></tr>
            <tr><td><strong>PHP</strong></td><td>8.2.12 (ZTS)</td><td>Motor de ejecución. Tipado estricto, match expressions, enums y fibras (características usadas en el proyecto).</td></tr>
            <tr><td><strong>barryvdh/laravel-dompdf</strong></td><td>v3.1.2</td><td>Wrapper de DOMPDF para Laravel. Convierte vistas Blade en documentos PDF descargables.</td></tr>
            <tr><td><strong>laravel/breeze</strong></td><td>—</td><td>Scaffolding de autenticación. Login, logout, registro y middleware de sesión configurados listos para usar.</td></tr>
            <tr><td><strong>phpunit/phpunit</strong></td><td>11.5.55</td><td>Framework de pruebas. Ejecuta los {{ $total_tests }} tests de integración que validan toda la lógica del sistema.</td></tr>
            <tr><td><strong>fakerphp/faker</strong></td><td>—</td><td>Generación de datos falsos realistas para factories de prueba (nombres, usernames, emails).</td></tr>
            <tr><td><strong>Carbon</strong></td><td>(Laravel built-in)</td><td>Manipulación avanzada de fechas. Usado para calcular períodos de vales vencidos, anomalías temporales y eventos.</td></tr>
            <tr><td><strong>Chart.js</strong></td><td>CDN (latest)</td><td>Gráficos doughnut interactivos de bugs por categoría y severidad en el panel de diagnóstico del NOC.</td></tr>
            <tr><td><strong>SweetAlert2</strong></td><td>CDN (latest)</td><td>Modales animados para confirmación de operaciones críticas, escaneos y retroalimentación al usuario.</td></tr>
            <tr><td><strong>FontAwesome 6</strong></td><td>CDN</td><td>Librería de iconografía usada en todo el panel: seguridad, diagnóstico, advertencias, badges de estado.</td></tr>
            <tr><td><strong>Bootstrap 5</strong></td><td>CDN</td><td>Sistema de grid y utilidades de layout CSS para la interfaz del NOC y la consola de análisis.</td></tr>
            <tr><td><strong>MySQL / MariaDB</strong></td><td>≥ 10.4</td><td>Motor de base de datos relacional. Soporta OPTIMIZE TABLE (usado por el protocolo de reparación automatizada).</td></tr>
        </tbody>
    </table>

    <!-- 3.8.4 Instrucciones -->
    <h2>3.8.4 Instrucciones para Ejecutar el Automatizador Cortex NOC</h2>

    <div class="callout warning">
        <p><strong>⚠ Prerequisito:</strong> Asegúrate de completar la instalación completa del proyecto (<span class="inline-code">composer install</span>, <span class="inline-code">php artisan migrate</span>, <span class="inline-code">php artisan db:seed</span>) antes de continuar.</p>
    </div>

    <ul class="steps-list">
        <li>
            <div class="step-num">1</div>
            <div class="step-text"><strong>Iniciar los servicios base</strong><br>
                Abre XAMPP y activa Apache + MySQL. O usa:<br>
                <span class="inline-code">php artisan serve</span> → <span class="inline-code">http://localhost:8000</span>
            </div>
        </li>
        <li>
            <div class="step-num">2</div>
            <div class="step-text"><strong>Verificar que las migraciones de Cortex están aplicadas</strong><br>
                <span class="inline-code">php artisan migrate</span> — Asegura que la tabla <span class="inline-code">cortex_events</span> exista.
            </div>
        </li>
        <li>
            <div class="step-num">3</div>
            <div class="step-text"><strong>Iniciar sesión como Administrador</strong><br>
                Usuario: <span class="inline-code">admin</span> | Contraseña: <span class="inline-code">Adm!n#2026$SecureX9</span>
            </div>
        </li>
        <li>
            <div class="step-num">4</div>
            <div class="step-text"><strong>Navegar al NOC Cortex</strong><br>
                <span class="inline-code">http://localhost/laravel_app/public/cortex</span>
            </div>
        </li>
        <li>
            <div class="step-num">5</div>
            <div class="step-text"><strong>Presionar "RE-INICIAR ESCANEO"</strong><br>
                El modal muestra el progreso en 5 etapas. Al terminar, redirige al NOC y descarga el reporte PDF automáticamente.
            </div>
        </li>
        <li>
            <div class="step-num">6</div>
            <div class="step-text"><strong>Verificar los Neural Logs</strong><br>
                Los eventos quedan en la pestaña <strong>Monitoreo → Neural Logs</strong> y en la tabla <span class="inline-code">cortex_events</span> de la BD.
            </div>
        </li>
        <li>
            <div class="step-num">7</div>
            <div class="step-text"><strong>Ejecutar la suite de pruebas automatizadas</strong><br>
                <span class="inline-code">php artisan test</span> — Todos los tests deben aparecer con <strong>PASS</strong>.
            </div>
        </li>
    </ul>

    <!-- 3.8.6 Prevención de Vulnerabilidades -->
    <h2>3.8.6 Prevención de Vulnerabilidades Básicas (XSS, SQLi, Sesiones, CSRF)</h2>
    <p>
        El sistema implementa mecanismos activos y configuraciones robustas para mitigar los principales riesgos de seguridad web:
    </p>

    <h3>1. Inyección SQL (SQLi) y Consultas Preparadas</h3>
    <p>
        El motor del catálogo utiliza enlaces de parámetros a nivel de controlador mediante Eloquent ORM y PDO. Al realizar búsquedas parametrizadas, las instrucciones SQL y los datos de entrada se envían por canales independientes. Las comillas simples (') y dobles (") se interpretan de forma segura como texto literal e inofensivo.
    </p>
    <div class="code-block"><span class="code-comment">// Consulta Segura Parametrizada (Eloquent)</span>
$herramientas = Herramienta::where('nombre', 'like', "%{$busqueda}%")
    ->orWhere('codigo', 'like', "%{$busqueda}%")
    ->get();</div>

    <h3>2. Cross-Site Scripting (XSS) mediante Middleware</h3>
    <p>
        Se ha desarrollado un middleware global de sanitización recursiva de inputs registrado en el pipeline de la aplicación (<span class="inline-code">XssSanitization.php</span>). Este intercepta el payload de todas las solicitudes HTTP entrantes (formularios, JSON, queries) y elimina cualquier etiqueta script o código ejecutable mediante la función <span class="inline-code">strip_tags()</span> antes de ser procesado por los controladores.
    </p>
    <div class="code-block"><span class="code-comment">// Middleware XssSanitization.php</span>
$input = $request->all();
array_walk_recursive($input, function (&$val) {
    if (is_string($val)) {
        $val = strip_tags($val);
    }
});
$request->merge($input);</div>

    <h3>3. Seguridad de Sesión y Cookies</h3>
    <p>
        La configuración en <span class="inline-code">config/session.php</span> establece medidas estrictas de mitigación:
        <br>· <strong>HttpOnly (http_only => true)</strong>: Impide el acceso a la cookie de sesión a través de scripts de cliente (JavaScript), previniendo el robo de sesión ante ataques XSS.
        <br>· <strong>SameSite (same_site => 'lax')</strong>: Mitiga ataques de CSRF (Cross-Site Request Forgery) restringiendo el envío de cookies en peticiones originadas en dominios externos.
    </p>

    <!-- 3.8.5 Requisitos -->
    <h2>3.8.7 Requisitos del Sistema</h2>
    <table>
        <thead><tr><th style="width:25%;">Componente</th><th style="width:35%;">Requerimiento Mínimo</th><th>Versión Probada</th></tr></thead>
        <tbody>
            <tr><td><strong>Sistema Operativo</strong></td><td>Windows 10/11 · Ubuntu 22.04 · macOS 13+</td><td>Windows 11 (entorno de desarrollo)</td></tr>
            <tr><td><strong>PHP</strong></td><td>≥ 8.1 con extensiones: <span class="inline-code">pdo_mysql</span>, <span class="inline-code">mbstring</span>, <span class="inline-code">openssl</span>, <span class="inline-code">fileinfo</span>, <span class="inline-code">gd</span></td><td>PHP 8.2.12 ZTS x64</td></tr>
            <tr><td><strong>MySQL / MariaDB</strong></td><td>≥ MySQL 5.7 / ≥ MariaDB 10.4</td><td>MariaDB 10.4 (XAMPP bundle)</td></tr>
            <tr><td><strong>Composer</strong></td><td>≥ 2.0</td><td>2.x</td></tr>
            <tr><td><strong>Node.js / npm</strong></td><td>Node ≥ 18.0 · npm ≥ 9.0</td><td>Node 18.x LTS</td></tr>
            <tr><td><strong>Navegador Web</strong></td><td>Chrome 100+ · Firefox 100+ · Edge 100+</td><td>Chrome 124 (pruebas de UI)</td></tr>
            <tr><td><strong>RAM Servidor</strong></td><td>≥ 256 MB disponibles para PHP</td><td>512 MB recomendado</td></tr>
            <tr><td><strong>Espacio en Disco</strong></td><td>≥ 200 MB (app + vendor + BD)</td><td>—</td></tr>
            <tr><td><strong>Permisos de SO</strong></td><td>Escritura en <span class="inline-code">storage/</span> y <span class="inline-code">bootstrap/cache/</span></td><td><span class="inline-code">chmod 775</span> en Linux/Mac</td></tr>
        </tbody>
    </table>

    <!-- ══════════════════════════════════════════════════════════════
         3.9 CONCLUSIONES
    ══════════════════════════════════════════════════════════════ -->
    <div class="page-break"></div>
    <div class="section-title">3.9 — Conclusiones</div>

    <!-- 3.9.1 Logros -->
    <h2>3.9.1 Resumen de Logros Alcanzados</h2>
    <table>
        <thead><tr><th style="width:5%;">#</th><th style="width:30%;">Logro</th><th>Descripción</th></tr></thead>
        <tbody>
            <tr><td style="text-align:center;font-weight:bold;color:#0056b3;">1</td><td><strong>CRUD completo de activos</strong></td><td>Gestión integral de herramientas con control de stock, almacenes, categorías y eliminación lógica (soft-delete).</td></tr>
            <tr><td style="text-align:center;font-weight:bold;color:#0056b3;">2</td><td><strong>Sistema de Vales digitales</strong></td><td>Flujo completo de préstamo y devolución con control de fechas límite, estados activos y mora automática.</td></tr>
            <tr><td style="text-align:center;font-weight:bold;color:#0056b3;">3</td><td><strong>Módulo Cortex NOC</strong></td><td>Panel de monitoreo inteligente con 4 pestañas: Monitoreo, Seguridad, Diagnóstico e Inteligencia predictiva.</td></tr>
            <tr><td style="text-align:center;font-weight:bold;color:#0056b3;">4</td><td><strong>Reportes PDF Automáticos</strong></td><td>Al completar el escaneo, se descarga automáticamente el reporte con métricas de tiempo, severidad y recomendaciones priorizadas.</td></tr>
            <tr><td style="text-align:center;font-weight:bold;color:#0056b3;">5</td><td><strong>Auditoría persistente</strong></td><td>Todos los eventos (SECURITY, DIAGNOSTIC, TRANSACTION) quedan registrados en <span class="inline-code">cortex_events</span> con metadatos JSON.</td></tr>
            <tr><td style="text-align:center;font-weight:bold;color:#0056b3;">6</td><td><strong>Suite de Pruebas Automatizadas</strong></td><td>{{ $total_tests }} tests de integración que validan autenticación, lógica del NOC y registro de eventos en base de datos.</td></tr>
        </tbody>
    </table>

    <!-- 3.9.2 Hipótesis -->
    <h2>3.9.2 Validación de la Hipótesis Inicial</h2>

    <div class="hypothesis-box">
        <div class="label">Hipótesis planteada</div>
        <blockquote>
            "La implementación de pruebas automatizadas en un sistema de gestión de almacén permite detectar fallas de lógica de negocio de forma temprana, reduciendo el costo de corrección y mejorando la confiabilidad del sistema antes de su despliegue."
        </blockquote>
        <p style="margin-top:8px;"><span class="badge badge-validated">✓ VALIDADA</span></p>
    </div>

    <p>Durante el desarrollo se presentó un caso concreto que valida la hipótesis:</p>
    <p>Al implementar las pruebas del módulo de autenticación (<span class="inline-code">AuthenticationTest</span>), PHPUnit detectó una <strong>discrepancia silenciosa</strong>: la contraseña definida en <span class="inline-code">UsuarioFactory.php</span> era <span class="inline-code">Admin123!</span>, mientras que el test esperaba <span class="inline-code">password</span>. En una prueba manual, el login funciona correctamente con el usuario seeded, por lo que la falla habría pasado <strong>completamente inadvertida</strong> hasta llegar a un entorno de CI/CD o producción.</p>
    <p>La detección temprana gracias a PHPUnit permitió corregir el problema en <strong>menos de 2 minutos</strong>, versus potencialmente horas de depuración en producción. Adicionalmente, el test <span class="inline-code">test_run_scan_logs_diagnostic_event_and_redirects</span> garantiza que el sistema de auditoría registre correctamente los eventos ante cualquier refactorización futura.</p>

    <!-- 3.9.3 Lecciones -->
    <h2>3.9.3 Lecciones Aprendidas</h2>
    <p style="color:#666;font-size:11px;margin-bottom:10px;">Técnicas:</p>

    <div class="lesson-card">
        <span class="num">01.</span><span class="title">Los Servicios desacoplan la lógica</span>
        <div class="body">Separar <span class="inline-code">CortexAuditorService</span>, <span class="inline-code">CortexSecurityService</span> y <span class="inline-code">SystemValidatorService</span> en clases independientes facilitó enormemente la escritura de pruebas unitarias, la reutilización del código y la comprensión del flujo del sistema.</div>
    </div>
    <div class="lesson-card">
        <span class="num">02.</span><span class="title">Las factories deben ser autoexplicativas</span>
        <div class="body">La discrepancia de contraseñas en <span class="inline-code">UsuarioFactory</span> demostró que las factories de prueba deben documentar explícitamente los valores que generan, especialmente para campos sensibles como contraseñas hasheadas con <span class="inline-code">bcrypt</span>.</div>
    </div>
    <div class="lesson-card">
        <span class="num">03.</span><span class="title">DOMPDF requiere CSS compatible</span>
        <div class="body">El motor de renderizado PDF no soporta Flexbox completo ni CSS Grid. Fue necesario usar tablas HTML para el layout de los reportes para garantizar compatibilidad universal con todas las versiones del generador.</div>
    </div>
    <div class="lesson-card">
        <span class="num">04.</span><span class="title">La descarga automática requiere timing</span>
        <div class="body">La descarga del PDF al finalizar el escaneo requirió un <span class="inline-code">setTimeout(fn, 500)</span> para evitar conflictos de renderizado entre el DOM, el modal de SweetAlert2 y la respuesta HTTP del servidor.</div>
    </div>

    <p style="color:#666;font-size:11px;margin-bottom:10px;margin-top:14px;">De Proceso:</p>
    <div class="lesson-card">
        <span class="num">05.</span><span class="title">Planificar antes de implementar</span>
        <div class="body">El uso de documentos de plan de implementación antes de modificar el código redujo la cantidad de rollbacks, mantuvo los cambios organizados y permitió una verificación sistemática de cada componente.</div>
    </div>
    <div class="lesson-card">
        <span class="num">06.</span><span class="title">Las pruebas son documentación viva</span>
        <div class="body">Tests como <span class="inline-code">test_run_scan_logs_diagnostic_event_and_redirects</span> documentan el comportamiento esperado del sistema de forma ejecutable, reduciendo la curva de aprendizaje para nuevos desarrolladores.</div>
    </div>

    <!-- 3.9.4 Mejoras Futuras -->
    <h2>3.9.4 Mejoras Futuras y Escalabilidad</h2>

    <table>
        <thead><tr><th style="width:12%;">Horizonte</th><th style="width:35%;">Mejora</th><th>Descripción</th></tr></thead>
        <tbody>
            <tr>
                <td rowspan="4" style="text-align:center;vertical-align:middle;"><span class="badge badge-cp">Corto<br>0–3 m</span></td>
                <td><strong>Notificaciones en tiempo real</strong></td>
                <td>Laravel Echo + Pusher para alertas críticas del NOC sin recarga de página.</td>
            </tr>
            <tr>
                <td><strong>Filtrado de reportes por fecha</strong></td>
                <td>Generar reportes de auditoría para rangos de fechas específicos desde la interfaz.</td>
            </tr>
            <tr>
                <td><strong>Roles granulares</strong></td>
                <td>Permisos por módulo: un supervisor puede ver el NOC pero no ejecutar reparaciones.</td>
            </tr>
            <tr>
                <td><strong>Exportar logs en CSV</strong></td>
                <td>Complementar la exportación PDF con descarga de <span class="inline-code">cortex_events</span> en CSV para análisis externo.</td>
            </tr>
            <tr>
                <td rowspan="4" style="text-align:center;vertical-align:middle;"><span class="badge badge-mp">Mediano<br>3–6 m</span></td>
                <td><strong>API REST documentada</strong></td>
                <td>Exponer el NOC como API REST con tokens de autenticación (Laravel Sanctum).</td>
            </tr>
            <tr>
                <td><strong>Dashboard de métricas históricas</strong></td>
                <td>Graficar tendencias de bugs y eventos de seguridad en el tiempo con datos reales de BD.</td>
            </tr>
            <tr>
                <td><strong>Sistema de tickets de incidencias</strong></td>
                <td>Vincular anomalías de Cortex con un sistema de seguimiento de tickets para resolución formal.</td>
            </tr>
            <tr>
                <td><strong>Notificaciones por correo</strong></td>
                <td>Enviar el reporte PDF automáticamente por correo al administrador usando Laravel Mail.</td>
            </tr>
            <tr>
                <td rowspan="4" style="text-align:center;vertical-align:middle;"><span class="badge badge-lp">Largo<br>6+ m</span></td>
                <td><strong>Microservicios</strong></td>
                <td>Separar el módulo Cortex como microservicio con colas de mensajes (Redis + Laravel Queues).</td>
            </tr>
            <tr>
                <td><strong>Machine Learning real</strong></td>
                <td>Reemplazar predicciones simuladas con modelos ML entrenados con datos históricos reales.</td>
            </tr>
            <tr>
                <td><strong>Multi-almacén / Multi-sucursal</strong></td>
                <td>Panel central para gestionar múltiples almacenes en ubicaciones geográficas distintas.</td>
            </tr>
            <tr>
                <td><strong>Auditoría inmutable</strong></td>
                <td>Migrar <span class="inline-code">cortex_events</span> a un log append-only o blockchain-lite para cumplimiento normativo.</td>
            </tr>
        </tbody>
    </table>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p>
            <span class="footer-brand">Cortex AI — Sistema Almacén Inteligente</span><br>
            Documentación Técnica generada automáticamente | {{ $date }} | Generado por: {{ $generated_by }}
        </p>
    </div>

</body>
</html>
