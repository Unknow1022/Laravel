<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset="utf-8">
    <title>Reporte de Bug Profesional</title>
    <style>
        body { font-family: 'Calibri', Arial, sans-serif; font-size: 11pt; line-height: 1.5; }
        h1 { font-size: 16pt; color: #2F5496; border-bottom: 1px solid #2F5496; padding-bottom: 5px; }
        h2 { font-size: 13pt; color: #2F5496; margin-top: 20px; }
        .bold { font-weight: bold; }
        .section { margin-bottom: 15px; }
        .image-placeholder {
            border: 2px dashed #999;
            background-color: #f0f0f0;
            width: 100%;
            height: 300px;
            display: table;
            margin: 20px 0;
            text-align: center;
        }
        .image-placeholder span {
            display: table-cell;
            vertical-align: middle;
            color: #666;
            font-size: 14pt;
        }
    </style>
</head>
<body>

    <h1>Reporte de Bug</h1>

    <div class="section">
        <span class="bold">Título:</span> La barra lateral de navegación no colapsa en vista móvil, causando superposición y corte de elementos en el contenido principal.
    </div>

    <div class="section">
        <span class="bold">Descripción Breve:</span> Al redimensionar la ventana del navegador a resoluciones de dispositivos móviles o tablets (ej. 629px de ancho), el menú lateral izquierdo ("Almacén") mantiene su tamaño fijo en lugar de ocultarse tras un menú hamburguesa o colapsar. Esto comprime severamente el área de contenido principal, provocando que textos, tablas y botones se superpongan o se corten, afectando gravemente la usabilidad.
    </div>

    <h2>Pasos para Reproducir</h2>
    <ol>
        <li>Iniciar sesión en el Sistema de Almacén con una cuenta válida (ej. Administrador General).</li>
        <li>Navegar a cualquier módulo principal, por ejemplo, "Vales de Salida" o "Cortex Assistant".</li>
        <li>Abrir las herramientas de desarrollador del navegador (F12) y activar la vista de diseño responsivo (Device Toolbar).</li>
        <li>Ajustar el ancho del viewport a resoluciones menores a 768px (por ejemplo, 629px como se observa en las pruebas).</li>
        <li>Observar el comportamiento del menú lateral y los elementos de la vista principal (botones de acción, tablas de datos, tarjetas de información).</li>
    </ol>

    <h2>Comportamiento Esperado</h2>
    <p>En resoluciones de pantallas pequeñas (tablets y móviles), la barra lateral de navegación debe colapsarse automáticamente o esconderse detrás de un botón de menú tipo "hamburguesa" para maximizar el espacio disponible para el contenido principal. Los elementos internos de las vistas (como la tabla de vales o los botones de acción rápida) deben fluir hacia abajo (stack) o ajustar su tamaño para evitar desbordamientos o superposiciones.</p>

    <h2>Comportamiento Actual</h2>
    <p>La barra lateral permanece estática y ocupa una porción significativa de la pantalla. Como resultado en la vista "Vales de Salida":</p>
    <ul>
        <li>El texto de la fecha superior se comprime y desalinea.</li>
        <li>El botón flotante (escudo) se superpone sobre el botón amarillo de "Devolución Rápida".</li>
        <li>Las columnas de la tabla de datos ("TRABAJADOR...") se cortan porque no hay suficiente espacio horizontal ni scroll adaptativo.</li>
    </ul>
    <p>En la vista "Cortex Assistant":</p>
    <ul>
        <li>Los textos de las tarjetas se envuelven de manera forzada y poco estética debido a la falta de espacio.</li>
    </ul>

    <h2>Severidad</h2>
    <p><span class="bold">Alta</span> (Afecta directamente la usabilidad del sistema en dispositivos móviles, impidiendo leer datos completos o interactuar cómodamente con los botones).</p>

    <h2>Evidencia</h2>
    <p><em>(Pega aquí la captura de pantalla de Vales de Salida)</em></p>
    <div class="image-placeholder">
        <span>[ ESPACIO PARA PEGAR IMAGEN 1: Vales de Salida ]</span>
    </div>

    <p><em>(Pega aquí la captura de pantalla de Cortex Assistant)</em></p>
    <div class="image-placeholder">
        <span>[ ESPACIO PARA PEGAR IMAGEN 2: Cortex Assistant ]</span>
    </div>

</body>
</html>
