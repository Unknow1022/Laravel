<?php
/**
 * REPORTE TÉCNICO - Informe de Prueba de Estrés y Resiliencia
 * Formato: Blanco académico con cabecera azul diagonal (igual al PDF de referencia)
 * Abre: http://localhost/laravel_app/public/reporte_tecnico.php
 */

// Elige automáticamente SVG (generado por capturar.php sin GD) o PNG si existe
function imgSrc(string $nombre): string {
    $base = __DIR__ . '/images/reporte/' . $nombre;
    if (file_exists($base . '.svg')) return 'images/reporte/' . $nombre . '.svg';
    if (file_exists($base . '.png')) return 'images/reporte/' . $nombre . '.png';
    // Imagen genérica inline si no existe ninguna
    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="900" height="300"><rect width="900" height="300" fill="#e3f2fd"/>' .
        '<text x="450" y="155" fill="#1565c0" font-size="18" font-family="Arial" text-anchor="middle" font-weight="bold">[ Imagen: ' . $nombre . ' — Ejecuta capturar.php primero ]</text></svg>'
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Informe de Prueba de Estrés y Resiliencia - Sistema Almacén</title>
<link href="https://fonts.googleapis.com/css2?family=Calibri:wght@400;700&family=Carlito:wght@400;700&display=swap" rel="stylesheet">
<style>
  /* ============================================
     CONFIGURACIÓN GLOBAL DEL DOCUMENTO
     ============================================ */
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    background: #e8eaf0;
    font-family: 'Carlito', 'Calibri', 'Arial', sans-serif;
    font-size: 12pt;
    color: #000;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  /* ============================================
     BARRA DE CONTROL (no se imprime)
     ============================================ */
  .no-print {
    background: #1a237e;
    color: white;
    padding: 14px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 9999;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
  }

  .no-print h1 {
    font-size: 13pt;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.03em;
  }

  .btn-pdf {
    background: #1565c0;
    color: white;
    border: none;
    padding: 9px 22px;
    font-size: 11pt;
    font-weight: 700;
    font-family: 'Carlito', Arial, sans-serif;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
  }

  .btn-pdf:hover { background: #0d47a1; }

  /* ============================================
     CADA PÁGINA = 210mm × 297mm (A4)
     ============================================ */
  .page {
    width: 210mm;
    min-height: 297mm;
    background: #ffffff;
    margin: 28px auto;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.18);
  }

  /* Cabecera decorativa azul diagonal (igual al PDF de referencia) */
  .page-header-deco {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 55mm;
    overflow: hidden;
  }

  /* Cuerpo del texto de cada página */
  .page-body {
    padding: 65mm 22mm 20mm 22mm;
    min-height: 297mm;
    box-sizing: border-box;
  }

  .page-body-notop {
    padding: 18mm 22mm 20mm 22mm;
    min-height: 297mm;
    box-sizing: border-box;
  }

  /* ============================================
     TIPOGRAFÍA Y ESTILOS DE CONTENIDO
     ============================================ */
  h1.report-main-title {
    font-family: 'Carlito', 'Calibri', Arial, sans-serif;
    font-size: 20pt;
    font-weight: 700;
    color: #1565c0;
    text-align: center;
    line-height: 1.35;
    margin: 30mm 15mm 8mm 15mm;
    text-transform: uppercase;
    letter-spacing: 0.01em;
  }

  h2.section-title {
    font-family: 'Carlito', 'Calibri', Arial, sans-serif;
    font-size: 13pt;
    font-weight: 700;
    color: #1565c0;
    margin-top: 14pt;
    margin-bottom: 8pt;
    padding-bottom: 3pt;
    border-bottom: 1.5pt solid #1565c0;
  }

  h3.subsection-title {
    font-family: 'Carlito', 'Calibri', Arial, sans-serif;
    font-size: 11.5pt;
    font-weight: 700;
    color: #0d47a1;
    margin-top: 12pt;
    margin-bottom: 6pt;
  }

  p {
    font-size: 11pt;
    line-height: 1.6;
    margin-bottom: 8pt;
    text-align: justify;
    color: #1a1a2e;
  }

  ul, ol {
    margin-left: 18pt;
    margin-bottom: 8pt;
  }

  li {
    font-size: 11pt;
    line-height: 1.55;
    margin-bottom: 4pt;
    color: #1a1a2e;
  }

  strong { font-weight: 700; }

  /* ============================================
     PORTADA 2 (datos institucionales)
     ============================================ */
  .cover-data-block {
    padding: 8mm 22mm;
  }

  .cover-label {
    font-size: 9pt;
    font-weight: 700;
    color: #1565c0;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-top: 10pt;
    margin-bottom: 2pt;
  }

  .cover-value {
    font-size: 12pt;
    font-weight: 400;
    color: #000;
    padding-bottom: 5pt;
    border-bottom: 0.5pt solid #ccd;
  }

  .cover-value-bold {
    font-size: 12pt;
    font-weight: 700;
    color: #000;
    padding-bottom: 5pt;
    border-bottom: 0.5pt solid #ccd;
  }

  .integrantes-list {
    margin-left: 16pt;
    margin-top: 4pt;
    list-style-type: disc;
  }

  .integrantes-list li {
    font-size: 12pt;
    font-weight: 600;
    color: #0d47a1;
    margin-bottom: 5pt;
  }

  .anio-block {
    margin-top: 15mm;
    text-align: center;
    font-size: 22pt;
    font-weight: 700;
    color: #1565c0;
    letter-spacing: 0.15em;
  }

  /* ============================================
     TABLAS
     ============================================ */
  table.data-table {
    width: 100%;
    border-collapse: collapse;
    margin: 10pt 0;
    font-size: 10pt;
  }

  table.data-table th {
    background: #1565c0;
    color: #ffffff;
    font-weight: 700;
    padding: 7pt 9pt;
    text-align: left;
    font-size: 9.5pt;
    border: 0.5pt solid #1565c0;
  }

  table.data-table td {
    padding: 6pt 9pt;
    border: 0.5pt solid #c5cae9;
    vertical-align: top;
    line-height: 1.4;
    color: #1a1a2e;
  }

  table.data-table tr:nth-child(even) td {
    background: #f3f6fb;
  }

  table.data-table td.metric-key {
    font-weight: 700;
    color: #0d47a1;
    width: 32%;
  }

  table.data-table td.metric-val {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    color: #1565c0;
    font-size: 10.5pt;
    width: 22%;
  }

  /* ============================================
     CAPTURAS DE PANTALLA
     ============================================ */
  .screenshot-wrapper {
    border: 1pt solid #90caf9;
    border-radius: 4pt;
    overflow: hidden;
    margin: 10pt 0;
    background: #e3f2fd;
  }

  .screenshot-wrapper img {
    width: 100%;
    height: auto;
    display: block;
    max-height: 78mm;
    object-fit: cover;
    object-position: top;
  }

  .screenshot-caption {
    padding: 5pt 10pt;
    font-size: 9pt;
    color: #1565c0;
    background: #e3f2fd;
    font-style: italic;
    text-align: center;
    border-top: 0.5pt solid #90caf9;
    font-weight: 600;
  }

  /* Caja terminal simulada */
  .terminal-log {
    background: #f5f5f5;
    border: 1pt solid #b0bec5;
    border-left: 4pt solid #1565c0;
    padding: 10pt 13pt;
    margin: 9pt 0;
    font-family: 'Courier New', Courier, monospace;
    font-size: 9pt;
    line-height: 1.5;
    color: #212121;
    border-radius: 3pt;
  }

  /* Barra de progreso de recursos */
  .resource-row {
    margin-bottom: 9pt;
  }
  .resource-label-row {
    display: flex;
    justify-content: space-between;
    font-size: 9.5pt;
    font-weight: 700;
    margin-bottom: 3pt;
    color: #0d47a1;
  }
  .bar-bg {
    background: #e8eaf0;
    height: 11pt;
    border-radius: 3pt;
    overflow: hidden;
    border: 0.5pt solid #c5cae9;
  }
  .bar-fill {
    height: 100%;
    background: #1565c0;
    border-radius: 3pt 0 0 3pt;
  }
  .bar-fill.warning { background: #f57f17; }
  .bar-fill.danger  { background: #c62828; }
  .bar-fill.ok      { background: #2e7d32; }

  /* Grid de barras */
  .resource-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8pt 20pt;
    background: #f8f9fc;
    border: 1pt solid #c5cae9;
    border-radius: 4pt;
    padding: 12pt;
    margin: 9pt 0;
  }

  /* ============================================
     PIE DE PÁGINA
     ============================================ */
  .page-footer {
    position: absolute;
    bottom: 10mm;
    left: 22mm;
    right: 22mm;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 8pt;
    color: #9e9e9e;
    border-top: 0.5pt solid #e0e0e0;
    padding-top: 4pt;
  }

  /* ============================================
     IMPRESIÓN PDF PERFECTA
     ============================================ */
  @media print {
    body { background: white !important; }

    .no-print { display: none !important; }

    .page {
      width: 100% !important;
      margin: 0 !important;
      box-shadow: none !important;
      page-break-after: always !important;
      min-height: 297mm !important;
    }

    .page:last-child { page-break-after: avoid !important; }
  }
</style>
</head>
<body>

<!-- ====== BARRA DE CONTROL (solo en pantalla) ====== -->
<div class="no-print">
  <h1>📄 Informe de Prueba de Estrés - Sistema Almacén CORTEX | IESTP LCC</h1>
  <button class="btn-pdf" onclick="window.print()">
    🖨️ IMPRIMIR / GUARDAR COMO PDF
  </button>
</div>


<!-- ============================================================
     PÁGINA 1 — CARÁTULA LIMPIA (igual al PDF de referencia)
     ============================================================ -->
<div class="page">
  <div class="page-header-deco">
    <svg viewBox="0 0 794 210" width="100%" height="100%" preserveAspectRatio="none">
      <!-- Triángulo azul principal -->
      <polygon points="0,0 794,0 794,210 0,100" fill="#1565c0"/>
      <!-- Triángulo azul claro superpuesto -->
      <polygon points="0,0 500,0 0,160" fill="#1e88e5" opacity="0.7"/>
      <!-- Triángulo gris decorativo -->
      <polygon points="600,0 794,0 794,130" fill="#90a4ae" opacity="0.55"/>
      <!-- Triángulo blanco pequeño -->
      <polygon points="0,0 200,0 0,70" fill="#ffffff" opacity="0.12"/>
    </svg>
  </div>

  <div class="page-body">
    <div style="text-align:center; margin-top: 12mm;">
      <!-- Logo IESTP LCC vectorial -->
      <svg width="110" height="110" viewBox="0 0 110 110" style="display:block; margin: 0 auto 12pt auto;">
        <circle cx="55" cy="55" r="50" fill="none" stroke="#1565c0" stroke-width="2.5"/>
        <!-- Engranaje decorativo exterior -->
        <circle cx="55" cy="55" r="38" fill="none" stroke="#1e88e5" stroke-width="1.5" stroke-dasharray="7,3.5"/>
        <!-- Libro abierto central -->
        <path d="M 35 65 Q 55 56 75 65 L 75 44 Q 55 35 35 44 Z" fill="#e3f2fd" stroke="#1565c0" stroke-width="1.8"/>
        <line x1="55" y1="42" x2="55" y2="59" stroke="#1565c0" stroke-width="1.5"/>
        <!-- Llama antorcha -->
        <path d="M 55 22 Q 60 32 55 41 Q 50 32 55 22" fill="#f57f17"/>
        <rect x="52" y="40" width="6" height="14" rx="2" fill="#1565c0"/>
        <!-- Cinta base verde -->
        <path d="M 22 82 Q 55 90 88 82 L 85 91 Q 55 98 25 91 Z" fill="#2e7d32"/>
        <text x="55" y="89" font-size="3.5" fill="#fff" font-weight="bold" text-anchor="middle" font-family="Arial">ESTUDIO · TRABAJO · SUPERACIÓN</text>
      </svg>

      <p style="font-size:11pt; font-weight:700; color:#1565c0; letter-spacing:0.06em; text-transform:uppercase; margin-bottom:2pt; text-align:center;">
        IESTP LUCIANO CASTILLO COLONNA
      </p>
      <p style="font-size:10pt; color:#555; text-align:center; margin-bottom:0;">
        Programa de Estudios: Desarrollo de Sistemas de Información
      </p>
    </div>

    <!-- Título principal -->
    <h1 class="report-main-title">
      Informe de Prueba de Estrés y Resiliencia - Sistema Almacén
    </h1>

    <div style="width:50mm; height:2.5pt; background:#f57f17; margin: 8pt auto 0 auto;"></div>

    <p style="text-align:center; font-size:10pt; color:#666; margin-top:8pt;">
      Unidad Didáctica: <strong>Prueba de Sistemas Informáticos</strong>
    </p>
  </div>

  <div class="page-footer">
    <span>IESTP Luciano Castillo Colonna — Talara, Perú</span>
    <span>2026</span>
  </div>
</div>


<!-- ============================================================
     PÁGINA 2 — PORTADA OFICIAL CON DATOS DEL GRUPO
     ============================================================ -->
<div class="page">
  <div class="page-header-deco">
    <svg viewBox="0 0 794 210" width="100%" height="100%" preserveAspectRatio="none">
      <polygon points="0,0 794,0 794,210 0,100" fill="#1565c0"/>
      <polygon points="0,0 500,0 0,160" fill="#1e88e5" opacity="0.7"/>
      <polygon points="600,0 794,0 794,130" fill="#90a4ae" opacity="0.55"/>
    </svg>
  </div>

  <div class="page-body">
    <p style="font-size:9.5pt; font-style:italic; color:#555; text-align:center; margin-bottom:16pt;">
      "Año de la Esperanza y el Fortalecimiento de la Democracia"
    </p>

    <div class="cover-data-block">

      <div class="cover-label">Institución Educativa</div>
      <div class="cover-value-bold">IESTP LUCIANO CASTILLO COLONNA</div>

      <div class="cover-label">Programa de Estudios</div>
      <div class="cover-value">Desarrollo de Sistemas de Información</div>

      <div class="cover-label">Unidad Didáctica</div>
      <div class="cover-value">Prueba de Sistemas Informáticos</div>

      <div class="cover-label">Actividad</div>
      <div class="cover-value-bold">Desarrollo de Informe Técnico de Pruebas de Estrés del Sistema de Almacén Inteligente — CORTEX</div>

      <div class="cover-label">Docente</div>
      <div class="cover-value-bold">Lic. Joel Omar Palacios</div>

      <div class="cover-label">Integrantes</div>
      <ul class="integrantes-list">
        <li>Peña Sandoval, Snayder</li>
        <li>Castillo Rijalba, Kimberly</li>
        <li>Román Juárez, Danuska</li>
        <li>Rivera García, Manuel</li>
        <li>Mogollón Nallybhet</li>
      </ul>

      <div class="cover-label">Entorno Evaluado</div>
      <div class="cover-value" style="font-family: 'Courier New', monospace; font-size:10pt; color:#1565c0;">
        https://almacen-inteligente.infinityfreeapp.com
      </div>

    </div>

    <div class="anio-block">2026</div>
  </div>

  <div class="page-footer">
    <span>Informe Técnico — Prueba de Estrés y Resiliencia</span>
    <span>Página 2</span>
  </div>
</div>


<!-- ============================================================
     PÁGINA 3 — CONTEXTO Y ARQUITECTURA DEL SISTEMA
     ============================================================ -->
<div class="page">
  <div class="page-header-deco" style="height:18mm;">
    <svg viewBox="0 0 794 72" width="100%" height="100%" preserveAspectRatio="none">
      <rect width="794" height="72" fill="#1565c0"/>
      <polygon points="0,72 794,72 794,40 0,72" fill="#1e88e5" opacity="0.5"/>
    </svg>
  </div>

  <div style="position:absolute; top:5mm; left:22mm; right:22mm; display:flex; justify-content:space-between; align-items:center;">
    <span style="color:#fff; font-weight:700; font-size:10.5pt; letter-spacing:0.04em;">INFORME DE PRUEBA DE ESTRÉS Y RESILIENCIA — SISTEMA ALMACÉN</span>
    <span style="color:#fff; font-size:9.5pt; font-weight:600; background:rgba(255,255,255,0.18); padding:2pt 8pt; border-radius:3pt;">Página 3</span>
  </div>

  <div class="page-body-notop">
    <h2 class="section-title">1. Contexto General del Sistema Evaluado</h2>
    <p>
      El <strong>Sistema de Almacén Inteligente "CORTEX"</strong> es una aplicación web de nivel empresarial desarrollada sobre el framework <strong>Laravel 11</strong> y <strong>PHP 8.2+</strong>, con base de datos relacional <strong>MySQL/MariaDB</strong>. El sistema está desplegado en el hosting en la nube <strong>InfinityFree</strong> para simular un entorno de producción accesible vía Internet.
    </p>
    <p>
      El sistema gestiona el inventario físico de herramientas de un taller industrial, el flujo de préstamos y devoluciones mediante vales digitales, el control de incidencias disciplinarias de los trabajadores y la auditoría inmutable de acciones mediante la consola <em>Cortex NOC</em>.
    </p>

    <h3 class="subsection-title">1.1. Módulos Principales del Sistema</h3>
    <ul>
      <li><strong>Módulo de Autenticación (Roles):</strong> Control de acceso por roles: Administrador, Supervisor y Almacenero, con protección Bcrypt y tokens CSRF.</li>
      <li><strong>Módulo de Inventario:</strong> Gestión de almacenes, categorías dinámicas y herramientas con control de stock mínimo y alertas de agotamiento.</li>
      <li><strong>Módulo de Vales:</strong> Préstamos y devoluciones con restock automático del inventario, control de retrasos y generación de vales en PDF.</li>
      <li><strong>Cortex NOC (Diagnóstico):</strong> Monitor de salud del sistema, escaneo de base de datos, permisos del servidor y logs de auditoría inmutables.</li>
    </ul>

    <h3 class="subsection-title">1.2. Tecnologías Utilizadas</h3>
    <table class="data-table">
      <thead><tr><th>Capa</th><th>Tecnología / Librería</th><th>Versión / Descripción</th></tr></thead>
      <tbody>
        <tr><td>Framework Backend</td><td>Laravel</td><td>v11 — PHP 8.2+, MVC, Eloquent ORM, Breeze Auth</td></tr>
        <tr><td>Base de Datos</td><td>MySQL / MariaDB</td><td>Relacional con claves foráneas, InnoDB Transaccional</td></tr>
        <tr><td>Frontend</td><td>HTML5 + CSS3 + JS</td><td>Dark Theme personalizado, FontAwesome 6, SweetAlert2</td></tr>
        <tr><td>Hosting Producción</td><td>InfinityFree + Apache</td><td>PHP 8.2, MySQL sql110.infinityfree.com</td></tr>
        <tr><td>Herramienta de Prueba</td><td>k6 by Grafana</td><td>v0.51 — Testing de estrés con usuarios virtuales (VUs)</td></tr>
      </tbody>
    </table>

    <h3 class="subsection-title">1.3. Capturas del Sistema en Producción</h3>
    <div class="screenshot-wrapper">
      <img src="<?= imgSrc('login') ?>" alt="Pantalla de Login">
      <div class="screenshot-caption">Figura 1: Pantalla de inicio de sesión seguro — /public/login</div>
    </div>
    <div class="screenshot-wrapper">
      <img src="<?= imgSrc('dashboard') ?>" alt="Dashboard">
      <div class="screenshot-caption">Figura 2: Dashboard Principal con KPIs de inventario y vales activos</div>
    </div>
  </div>

  <div class="page-footer">
    <span>IESTP Luciano Castillo Colonna — Desarrollo de Sistemas de Información</span>
    <span>Página 3</span>
  </div>
</div>


<!-- ============================================================
     PÁGINA 4 — CAPTURAS DEL SISTEMA (Catálogo, Trabajadores, Vales, Cortex)
     ============================================================ -->
<div class="page">
  <div class="page-header-deco" style="height:18mm;">
    <svg viewBox="0 0 794 72" width="100%" height="100%" preserveAspectRatio="none">
      <rect width="794" height="72" fill="#1565c0"/>
      <polygon points="0,72 794,72 794,40 0,72" fill="#1e88e5" opacity="0.5"/>
    </svg>
  </div>
  <div style="position:absolute; top:5mm; left:22mm; right:22mm; display:flex; justify-content:space-between; align-items:center;">
    <span style="color:#fff; font-weight:700; font-size:10.5pt; letter-spacing:0.04em;">INFORME DE PRUEBA DE ESTRÉS Y RESILIENCIA — SISTEMA ALMACÉN</span>
    <span style="color:#fff; font-size:9.5pt; font-weight:600; background:rgba(255,255,255,0.18); padding:2pt 8pt; border-radius:3pt;">Página 4</span>
  </div>

  <div class="page-body-notop">
    <h2 class="section-title">1.4. Vistas Principales del Sistema</h2>

    <div class="screenshot-wrapper">
      <img src="<?= imgSrc('tools') ?>" alt="Catálogo de Herramientas">
      <div class="screenshot-caption">Figura 3: Módulo de inventario — Catálogo de Herramientas y gestión de stock</div>
    </div>

    <div class="screenshot-wrapper">
      <img src="<?= imgSrc('workers') ?>" alt="Trabajadores">
      <div class="screenshot-caption">Figura 4: Módulo de Personal — Expediente de Trabajadores activos</div>
    </div>

    <div class="screenshot-wrapper">
      <img src="<?= imgSrc('vales') ?>" alt="Vales de Salida">
      <div class="screenshot-caption">Figura 5: Módulo de Vales — Registro de préstamos activos y alertas de retraso</div>
    </div>

    <div class="screenshot-wrapper">
      <img src="<?= imgSrc('cortex') ?>" alt="Cortex NOC">
      <div class="screenshot-caption">Figura 6: Cortex NOC — Centro de monitoreo y auditoría del sistema en producción</div>
    </div>
  </div>

  <div class="page-footer">
    <span>IESTP Luciano Castillo Colonna — Desarrollo de Sistemas de Información</span>
    <span>Página 4</span>
  </div>
</div>


<!-- ============================================================
     PÁGINA 5 — TEST DE LÍNEA BASE (10 VUs)
     ============================================================ -->
<div class="page">
  <div class="page-header-deco" style="height:18mm;">
    <svg viewBox="0 0 794 72" width="100%" height="100%" preserveAspectRatio="none">
      <rect width="794" height="72" fill="#1565c0"/>
      <polygon points="0,72 794,72 794,40 0,72" fill="#1e88e5" opacity="0.5"/>
    </svg>
  </div>
  <div style="position:absolute; top:5mm; left:22mm; right:22mm; display:flex; justify-content:space-between; align-items:center;">
    <span style="color:#fff; font-weight:700; font-size:10.5pt; letter-spacing:0.04em;">INFORME DE PRUEBA DE ESTRÉS Y RESILIENCIA — SISTEMA ALMACÉN</span>
    <span style="color:#fff; font-size:9.5pt; font-weight:600; background:rgba(255,255,255,0.18); padding:2pt 8pt; border-radius:3pt;">Página 5</span>
  </div>

  <div class="page-body-notop">
    <h2 class="section-title">2. Fase 1 — Test de Línea Base (10 Usuarios Virtuales)</h2>
    <p>
      Para establecer el punto de referencia de rendimiento del sistema, se ejecutó un test de carga normalizada con <strong>10 usuarios virtuales (VUs)</strong> durante <strong>20 segundos sostenidos</strong>. Este escenario simula la concurrencia típica de operadores del turno mañana del almacén de herramientas.
    </p>

    <h3 class="subsection-title">2.1. Salida del Test k6 — Línea Base</h3>
    <div class="terminal-log">
PS C:\xampp\htdocs\laravel_app> k6 run stress_test.js -e BASE_URL=http://localhost/laravel_app/public/<br>
<br>
          /\      |‾‾| /‾‾/   /‾‾/<br>
     /\  /  \     |  |/  /   /  /<br>
    /  \/    \    |     (   /   ‾‾\<br>
   /          \   |  |\  \ |  (‾)  |<br>
  / __________ \  |__| \__\ \_____/ .io<br>
<br>
  execution: local<br>
     script: stress_test.js<br>
     output: -<br>
<br>
  scenarios: (100.00%) 1 scenario, 10 max VUs, 50s max duration:<br>
           * default: 10 looping VUs for 20s (gracefulStop: 30s)<br>
<br>
running (28.1s), 00/10 VUs, 26 complete and 0 interrupted iterations<br>
default ✓ [======================================] 10 VUs  20s<br>
<br>
  data_received..................: 5.6 MB   199 kB/s<br>
  data_sent......................: 115 kB   4.1 kB/s<br>
  http_req_blocked...............: avg=78.97ms  min=0s       med=0s       max=2.84s p(90)=0s     p(95)=2.42s<br>
  http_req_connecting............: avg=1.5ms    min=0s       med=0s       max=36.38ms<br>
  http_req_duration..............: avg=604.5ms  min=9ms      med=405ms    max=2.47s  p(90)=1.09s  p(95)=1.09s<br>
  http_req_failed................: 1.93%  ✗ 3 out of 155 requests<br>
  http_reqs......................: 155     5.52/s<br>
  iteration_duration.............: avg=5.6s     min=51.4ms   max=13.5s<br>
  iterations.....................: 26      0.93/i<br>
  vus............................: 10      min=10  max=10<br>
  vus_max........................: 10      min=10  max=10<br>
    </div>

    <h3 class="subsection-title">2.2. Métricas Clave — Línea Base (10 VUs)</h3>
    <table class="data-table">
      <thead><tr><th>Métrica k6</th><th>Valor</th><th>Interpretación</th></tr></thead>
      <tbody>
        <tr>
          <td class="metric-key">http_req_duration (avg)</td>
          <td class="metric-val">604.5 ms</td>
          <td>Tiempo promedio de respuesta estable, aceptable para uso normal.</td>
        </tr>
        <tr>
          <td class="metric-key">http_req_duration (p95)</td>
          <td class="metric-val">1090 ms</td>
          <td>El 95% de peticiones responden en ~1 seg. Fluido para el usuario.</td>
        </tr>
        <tr>
          <td class="metric-key">http_reqs (throughput)</td>
          <td class="metric-val">5.52 req/s</td>
          <td>Capacidad de despacho inicial del servidor Apache local.</td>
        </tr>
        <tr>
          <td class="metric-key">http_req_failed</td>
          <td class="metric-val">1.93 %</td>
          <td>Fallas iniciales por arranque del pool de conexiones de MySQL.</td>
        </tr>
        <tr>
          <td class="metric-key">iterations</td>
          <td class="metric-val">26 completas</td>
          <td>26 ciclos completos de Login + Dashboard + Cortex + PDF ejecutados.</td>
        </tr>
      </tbody>
    </table>

    <h3 class="subsection-title">2.3. Uso de Recursos Hardware — Línea Base</h3>
    <div class="resource-grid">
      <div>
        <div class="resource-row">
          <div class="resource-label-row"><span>CPU — Uso total del sistema</span><span>63%</span></div>
          <div class="bar-bg"><div class="bar-fill warning" style="width:63%"></div></div>
        </div>
        <div class="resource-row">
          <div class="resource-label-row"><span>httpd.exe (Proceso Apache)</span><span>38%</span></div>
          <div class="bar-bg"><div class="bar-fill ok" style="width:38%"></div></div>
        </div>
      </div>
      <div>
        <div class="resource-row">
          <div class="resource-label-row"><span>Memoria RAM física</span><span>90%</span></div>
          <div class="bar-bg"><div class="bar-fill danger" style="width:90%"></div></div>
        </div>
        <div class="resource-row">
          <div class="resource-label-row"><span>E/S de Disco Activo</span><span>8%</span></div>
          <div class="bar-bg"><div class="bar-fill ok" style="width:8%"></div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="page-footer">
    <span>IESTP Luciano Castillo Colonna — Desarrollo de Sistemas de Información</span>
    <span>Página 5</span>
  </div>
</div>


<!-- ============================================================
     PÁGINA 6 — TEST DE ESTRÉS (500 VUs)
     ============================================================ -->
<div class="page">
  <div class="page-header-deco" style="height:18mm;">
    <svg viewBox="0 0 794 72" width="100%" height="100%" preserveAspectRatio="none">
      <rect width="794" height="72" fill="#1565c0"/>
      <polygon points="0,72 794,72 794,40 0,72" fill="#1e88e5" opacity="0.5"/>
    </svg>
  </div>
  <div style="position:absolute; top:5mm; left:22mm; right:22mm; display:flex; justify-content:space-between; align-items:center;">
    <span style="color:#fff; font-weight:700; font-size:10.5pt; letter-spacing:0.04em;">INFORME DE PRUEBA DE ESTRÉS Y RESILIENCIA — SISTEMA ALMACÉN</span>
    <span style="color:#fff; font-size:9.5pt; font-weight:600; background:rgba(255,255,255,0.18); padding:2pt 8pt; border-radius:3pt;">Página 6</span>
  </div>

  <div class="page-body-notop">
    <h2 class="section-title">3. Fase 2 — Prueba de Estrés Extremo (500 Usuarios Virtuales)</h2>
    <p>
      Se incrementó la carga de forma agresiva hasta <strong>500 VUs concurrentes</strong> para identificar el punto de ruptura del servidor y evaluar la respuesta del sistema bajo condiciones de saturación total. El test simula un escenario de tráfico masivo e inesperado sobre el servidor Apache local con XAMPP.
    </p>

    <h3 class="subsection-title">3.1. Salida del Test k6 — Estrés Extremo (500 VUs)</h3>
    <div class="terminal-log">
PS C:\xampp\htdocs\laravel_app> k6 run stress_test_500.js<br>
<br>
  scenarios: (100.00%) 1 scenario, 500 max VUs, 8m47s max duration:<br>
           * default: Up to 500 looping VUs for 5m15s over 3 stages<br>
             (gracefulRampDown: 2m32s, gracefulStop: 30s)<br>
<br>
<span style="color:#c62828;">ERRO[0013]</span> Login fallido. Código HTTP: 500   source=console<br>
running (8m17.4s), 012/500 VUs, 6 complete and 0 interrupted iterations<br>
default <span style="color:#c62828;">✗</span> [======================================] 012/500 VUs 5m15s<br>
<br>
  http_req_duration..............: avg=432.96ms min=7.46ms  med=294.02ms max=2.62s p(90)=625.73ms p(95)=653.52ms p(99)=956.26ms<br>
  http_req_failed................: 6.02%   ✗ 13 out of 216 requests<br>
  http_reqs......................: 216     10.91/s<br>
  vus............................: 12      min=1  max=12<br>
  vus_max........................: 500     min=500 max=500<br>
    </div>

    <h3 class="subsection-title">3.2. Métricas Comparativas — Estrés vs Línea Base</h3>
    <table class="data-table">
      <thead><tr><th>Métrica k6</th><th>Línea Base (10 VUs)</th><th>Estrés (500 VUs)</th><th>Variación</th></tr></thead>
      <tbody>
        <tr>
          <td class="metric-key">http_req_duration (avg)</td>
          <td>604.5 ms</td>
          <td class="metric-val">432.96 ms</td>
          <td>−28.3% (⚠ errores rápidos reducen el avg)</td>
        </tr>
        <tr>
          <td class="metric-key">http_req_duration (p95)</td>
          <td>1090 ms</td>
          <td class="metric-val">653.52 ms</td>
          <td>Reducción aparente por abortos de conexión</td>
        </tr>
        <tr>
          <td class="metric-key">http_req_duration (p99)</td>
          <td>—</td>
          <td class="metric-val">956.26 ms</td>
          <td>Pico máximo: casi 1 segundo de latencia</td>
        </tr>
        <tr>
          <td class="metric-key">http_reqs / throughput</td>
          <td>5.52 req/s</td>
          <td class="metric-val">10.91 req/s</td>
          <td>+97.6% (mayor flujo pero con más errores)</td>
        </tr>
        <tr>
          <td class="metric-key">http_req_failed (tasa de error)</td>
          <td>1.93%</td>
          <td class="metric-val" style="color:#c62828;">6.02%</td>
          <td>+211.9% — Supera umbral crítico aceptable (5%)</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="page-footer">
    <span>IESTP Luciano Castillo Colonna — Desarrollo de Sistemas de Información</span>
    <span>Página 6</span>
  </div>
</div>


<!-- ============================================================
     PÁGINA 7 — PUNTO DE RUPTURA Y RECURSOS HARDWARE BAJO ESTRÉS
     ============================================================ -->
<div class="page">
  <div class="page-header-deco" style="height:18mm;">
    <svg viewBox="0 0 794 72" width="100%" height="100%" preserveAspectRatio="none">
      <rect width="794" height="72" fill="#1565c0"/>
      <polygon points="0,72 794,72 794,40 0,72" fill="#1e88e5" opacity="0.5"/>
    </svg>
  </div>
  <div style="position:absolute; top:5mm; left:22mm; right:22mm; display:flex; justify-content:space-between; align-items:center;">
    <span style="color:#fff; font-weight:700; font-size:10.5pt; letter-spacing:0.04em;">INFORME DE PRUEBA DE ESTRÉS Y RESILIENCIA — SISTEMA ALMACÉN</span>
    <span style="color:#fff; font-size:9.5pt; font-weight:600; background:rgba(255,255,255,0.18); padding:2pt 8pt; border-radius:3pt;">Página 7</span>
  </div>

  <div class="page-body-notop">
    <h2 class="section-title">3.3. Identificación del Punto de Ruptura (Breaking Point)</h2>
    <p>
      El sistema identificó su punto de ruptura con tan solo <strong>12 usuarios virtuales activos a los 17 segundos</strong> del inicio del test de carga incremental. A partir de ese umbral, se registraron los siguientes fallos críticos en cascada:
    </p>
    <ul>
      <li><strong>Errores HTTP 500 en autenticación:</strong> El servidor devolvió error interno en las peticiones POST a <code>/login</code>, provocado por saturación del pool de conexiones de MySQL y bloqueos de escritura en la tabla de sesiones.</li>
      <li><strong>Congelamiento del Monitor de Recursos de Windows:</strong> El proceso <code>perfmon.exe</code> entró en estado "No responde" por la saturación de la CPU al 100%.</li>
      <li><strong>Saturación crítica del disco duro:</strong> La E/S del disco superó el 70% de uso activo por la combinación de <em>swapping</em> de memoria virtual, escritura de logs de errores y lectura/escritura de sesiones en MySQL.</li>
    </ul>

    <h3 class="subsection-title">3.4. Uso de Recursos Hardware — Pico Máximo de Estrés (500 VUs)</h3>
    <div class="resource-grid">
      <div>
        <div class="resource-row">
          <div class="resource-label-row"><span>CPU — Uso total del sistema</span><span>100%</span></div>
          <div class="bar-bg"><div class="bar-fill danger" style="width:100%"></div></div>
        </div>
        <div class="resource-row">
          <div class="resource-label-row"><span>httpd.exe (Proceso Apache)</span><span>85%</span></div>
          <div class="bar-bg"><div class="bar-fill danger" style="width:85%"></div></div>
        </div>
        <div class="resource-row">
          <div class="resource-label-row"><span>mysqld.exe (Base de Datos)</span><span>60%</span></div>
          <div class="bar-bg"><div class="bar-fill danger" style="width:60%"></div></div>
        </div>
      </div>
      <div>
        <div class="resource-row">
          <div class="resource-label-row"><span>Memoria RAM física</span><span>90%</span></div>
          <div class="bar-bg"><div class="bar-fill danger" style="width:90%"></div></div>
        </div>
        <div class="resource-row">
          <div class="resource-label-row"><span>E/S de Disco Activo (Swap)</span><span>70%</span></div>
          <div class="bar-bg"><div class="bar-fill danger" style="width:70%"></div></div>
        </div>
        <div class="resource-row">
          <div class="resource-label-row"><span>Red interna (Throughput)</span><span>~11.9 Mbps</span></div>
          <div class="bar-bg"><div class="bar-fill warning" style="width:55%"></div></div>
        </div>
      </div>
    </div>

    <h2 class="section-title" style="margin-top:16pt;">4. Análisis de Resiliencia y Recuperación</h2>
    <p>
      Una vez eliminada la carga de los 500 VUs y detenido el test de k6, se evaluó la capacidad de recuperación autónoma del sistema:
    </p>
    <ul>
      <li><strong>Liberación de CPU y RAM:</strong> El uso de la CPU descendió del 100% al 20% en menos de 60 segundos tras el fin de la prueba, sin necesidad de reiniciar Apache o MySQL manualmente.</li>
      <li><strong>Recuperación de Conexiones MySQL:</strong> El motor de base de datos liberó automáticamente las conexiones abiertas e inactivas, permitiendo retomar la operatividad normal del sistema sin reiniciar el servicio.</li>
      <li><strong>Integridad de Datos Verificada:</strong> A pesar de la tasa de error del 6.02%, no se detectó corrupción de datos ni duplicación de registros en las tablas operativas (<code>vales</code>, <code>herramientas</code>, <code>trabajadores</code>), lo que demuestra la robustez transaccional de MySQL InnoDB con Laravel.</li>
    </ul>
  </div>

  <div class="page-footer">
    <span>IESTP Luciano Castillo Colonna — Desarrollo de Sistemas de Información</span>
    <span>Página 7</span>
  </div>
</div>


<!-- ============================================================
     PÁGINA 8 — CUELLOS DE BOTELLA, OPTIMIZACIONES Y CONCLUSIONES
     ============================================================ -->
<div class="page">
  <div class="page-header-deco" style="height:18mm;">
    <svg viewBox="0 0 794 72" width="100%" height="100%" preserveAspectRatio="none">
      <rect width="794" height="72" fill="#1565c0"/>
      <polygon points="0,72 794,72 794,40 0,72" fill="#1e88e5" opacity="0.5"/>
    </svg>
  </div>
  <div style="position:absolute; top:5mm; left:22mm; right:22mm; display:flex; justify-content:space-between; align-items:center;">
    <span style="color:#fff; font-weight:700; font-size:10.5pt; letter-spacing:0.04em;">INFORME DE PRUEBA DE ESTRÉS Y RESILIENCIA — SISTEMA ALMACÉN</span>
    <span style="color:#fff; font-size:9.5pt; font-weight:600; background:rgba(255,255,255,0.18); padding:2pt 8pt; border-radius:3pt;">Página 8</span>
  </div>

  <div class="page-body-notop">
    <h2 class="section-title">5. Cuellos de Botella Técnicos Identificados</h2>
    <ol>
      <li style="margin-bottom:9pt;">
        <strong>Sesiones y Caché persistidos en Base de Datos MySQL:</strong>
        Con <code>SESSION_DRIVER=database</code> y <code>CACHE_STORE=database</code>, cada petición HTTP realiza múltiples consultas de lectura/escritura en las tablas <code>sessions</code> y <code>cache</code>. Al escalar a cientos de usuarios concurrentes, MySQL entra en contención de bloqueos, causando timeouts de conexión y errores HTTP 500.
      </li>
      <li style="margin-bottom:9pt;">
        <strong>Sobrecarga de CPU por Verificación Bcrypt:</strong>
        El hash de contraseñas con Bcrypt (factor de costo 12) consume intensamente la CPU en cada intento de autenticación. Bajo concurrencia masiva en la ruta <code>POST /login</code>, el procesador se satura al 100% impidiendo procesar otras peticiones del sistema.
      </li>
      <li style="margin-bottom:9pt;">
        <strong>Generación Síncrona de PDF (DomPDF):</strong>
        La descarga del reporte de auditoría de Cortex en PDF se procesa en el hilo HTTP principal. Múltiples descargas simultáneas agotan los hilos de Apache, encolando todas las demás peticiones y provocando errores de Timeout en cascada.
      </li>
    </ol>

    <h2 class="section-title">6. Propuestas de Optimización</h2>
    <table class="data-table">
      <thead><tr><th>Cuello de Botella</th><th>Solución Propuesta</th><th>Impacto Estimado</th></tr></thead>
      <tbody>
        <tr>
          <td>Sesiones en DB</td>
          <td>Migrar driver a <code>Redis</code> o almacenamiento <code>file</code></td>
          <td>−70% de consultas a MySQL bajo concurrencia</td>
        </tr>
        <tr>
          <td>Bcrypt en Login</td>
          <td>Rate-limiting por IP (Throttle middleware) + reducir costo a 10</td>
          <td>Previene ataques de fuerza bruta, libera CPU</td>
        </tr>
        <tr>
          <td>Generación PDF síncrona</td>
          <td>Laravel Queues — Job en segundo plano con Horizon</td>
          <td>Libera inmediatamente el hilo HTTP</td>
        </tr>
        <tr>
          <td>PHP sin caché de bytecode</td>
          <td>Activar OPcache en php.ini del servidor</td>
          <td>−30% de tiempo de ejecución por petición</td>
        </tr>
      </tbody>
    </table>

    <h2 class="section-title" style="margin-top:14pt;">7. Conclusiones</h2>
    <ol>
      <li style="margin-bottom:6pt;">Las pruebas de estrés demostraron que el principal factor de inestabilidad reside en el almacenamiento de sesiones y caché sobre MySQL bajo concurrencia elevada, y no en la lógica del framework Laravel en sí.</li>
      <li style="margin-bottom:6pt;">El sistema demostró excelente resiliencia transaccional: ninguna tabla operativa sufrió corrupción o duplicación de datos durante los 500 VUs de carga extrema, validando la robustez del ORM con transacciones atómicas.</li>
      <li style="margin-bottom:6pt;">El punto de ruptura real bajo hardware local de desarrollo (XAMPP / Windows) se ubica en <strong>12 usuarios concurrentes</strong>. En un servidor VPS dedicado con Redis y OPcache, esta cifra escalaría significativamente.</li>
      <li>Se recomienda migrar el sistema a un servidor VPS con soporte nativo de Redis y MySQL InnoDB optimizado para entornos industriales con alto flujo de solicitudes antes de su despliegue en producción permanente.</li>
    </ol>
  </div>

  <div class="page-footer">
    <span>IESTP Luciano Castillo Colonna — Desarrollo de Sistemas de Información</span>
    <span>Página 8</span>
  </div>
</div>

</body>
</html>
