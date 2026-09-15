<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Auditoría de Sistema - Cortex</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 13px;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0056b3;
            margin: 0;
            font-size: 22px;
        }
        .meta-info {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        h2 {
            color: #0056b3;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 7px 10px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        .status-pass { color: #28a745; font-weight: bold; }
        .status-fail { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        
        .badge-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        .badge-optimo {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .badge-fallas {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .summary-box table {
            margin-bottom: 0;
            border: none;
        }
        .summary-box td {
            border: none;
            padding: 4px 8px;
        }
        .summary-title {
            font-weight: bold;
            color: #495057;
            width: 30%;
        }
        
        .priority-Critica {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
            text-align: center;
        }
        .priority-Alta {
            background-color: #fff3cd;
            color: #856404;
            font-weight: bold;
            text-align: center;
        }
        .priority-Media {
            background-color: #cce5ff;
            color: #004085;
            font-weight: bold;
            text-align: center;
        }
        .priority-Baja {
            background-color: #e2e3e5;
            color: #383d41;
            font-weight: bold;
            text-align: center;
        }

        .chart-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .bar-row {
            margin-bottom: 6px;
        }
        .bar-label {
            display: inline-block;
            width: 25%;
            font-size: 11px;
            vertical-align: middle;
        }
        .bar-wrapper {
            display: inline-block;
            width: 65%;
            background-color: #eee;
            height: 12px;
            vertical-align: middle;
        }
        .bar-fill {
            height: 100%;
            background-color: #007bff;
        }
        .bar-value {
            display: inline-block;
            width: 8%;
            text-align: right;
            font-size: 11px;
            vertical-align: middle;
            font-weight: bold;
        }
        /* Colores por severidad */
        .fill-Critico { background-color: #dc3545; }
        .fill-Alto { background-color: #fd7e14; }
        .fill-Medio { background-color: #ffc107; }
        .fill-Bajo { background-color: #17a2b8; }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Reporte de Auditoría de Sistema - Cortex AI</h1>
        <div class="meta-info">
            Generado por: {{ $generated_by }} | Fecha: {{ $date }}
        </div>
    </div>

    <!-- RESUMEN EJECUTIVO -->
    <div class="summary-box">
        <h3 style="margin-top: 0; color: #0056b3; border-bottom: 1px solid #ddd; padding-bottom: 4px; font-size: 14px; margin-bottom: 8px;">Resumen Ejecutivo de Diagnóstico</h3>
        <table style="width: 100%;">
            <tr>
                <td class="summary-title">Estado General:</td>
                <td>
                    @if($systemStatus === 'ÓPTIMO')
                        <span class="badge-status badge-optimo">ÓPTIMO</span>
                    @else
                        <span class="badge-status badge-fallas">CON FALLAS</span>
                    @endif
                </td>
                <td class="summary-title">Total Bugs Detectados:</td>
                <td style="font-size: 14px; font-weight: bold; color: #dc3545;">
                    {{ array_sum($bugsBySeverity) }}
                </td>
            </tr>
            <tr>
                <td class="summary-title">Tiempo de Escaneo:</td>
                <td style="font-weight: bold; color: #007bff;">{{ $scanTime }}</td>
                <td class="summary-title">Memoria Utilizada:</td>
                <td style="font-weight: bold;">{{ $memoryUsed }}</td>
            </tr>
            <tr>
                <td class="summary-title">Carga Promedio CPU:</td>
                <td style="font-weight: bold;">{{ $cpuLoad }}</td>
                <td class="summary-title">Entorno del Sistema:</td>
                <td style="text-transform: uppercase; font-weight: bold; color: #6c757d;">{{ $environment }}</td>
            </tr>
        </table>
    </div>

    <h2>1. Diagnóstico de Salud de Componentes</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Componente</th>
                <th style="width: 15%;">Estado</th>
                <th style="width: 60%;">Mensaje / Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($diagnostic as $component => $result)
            <tr>
                <td style="text-transform: capitalize; font-weight: bold; color: #495057;">{{ $component }}</td>
                <td class="status-{{ strtolower($result['status']) }}">{{ $result['status'] }}</td>
                <td>{{ $result['message'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2>2. Distribución de Bugs por Nivel de Severidad</h2>
    <div class="chart-container">
        @php $maxSeverity = !empty($bugsBySeverity) ? max($bugsBySeverity) : 0; @endphp
        @foreach($bugsBySeverity as $severity => $count)
        <div class="bar-row">
            <span class="bar-label">{{ $severity }}</span>
            <div class="bar-wrapper">
                @php 
                    $width = $maxSeverity > 0 ? ($count / $maxSeverity) * 100 : 0; 
                    $cleanSev = str_replace('í', 'i', $severity);
                @endphp
                <div class="bar-fill fill-{{ $cleanSev }}" style="width: {{ $width }}%;"></div>
            </div>
            <span class="bar-value">{{ $count }} u.</span>
        </div>
        @endforeach
    </div>

    <h2>3. Distribución de Bugs por Categoría</h2>
    <div class="chart-container">
        @php $maxCategory = !empty($bugsByCategory) ? max($bugsByCategory) : 0; @endphp
        @foreach($bugsByCategory as $category => $count)
        <div class="bar-row">
            <span class="bar-label">{{ $category }}</span>
            <div class="bar-wrapper">
                @php $width = $maxCategory > 0 ? ($count / $maxCategory) * 100 : 0; @endphp
                <div class="bar-fill" style="width: {{ $width }}%;"></div>
            </div>
            <span class="bar-value">{{ $count }} u.</span>
        </div>
        @endforeach
    </div>

    <h2>4. Recomendaciones de Corrección Priorizadas</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 15%; text-align: center;">Prioridad</th>
                <th style="width: 25%;">Componente</th>
                <th style="width: 60%;">Acción de Corrección Recomendada</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recommendations as $rec)
            <tr>
                <td class="priority-{{ str_replace('í', 'i', $rec['prioridad']) }}">{{ $rec['prioridad'] }}</td>
                <td style="font-weight: bold; color: #495057;">{{ $rec['componente'] }}</td>
                <td>{{ $rec['accion'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2>5. Últimos Incidentes Críticos Registrados</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 20%;">Categoría</th>
                <th style="width: 15%;">Severidad</th>
                <th style="width: 50%;">Descripción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentIncidents as $incident)
            <tr>
                <td>{{ $incident['fecha'] }}</td>
                <td>{{ $incident['categoria'] }}</td>
                <td style="font-weight: bold; color: {{ $incident['severidad'] === 'Crítico' ? '#dc3545' : ($incident['severidad'] === 'Alto' ? '#fd7e14' : '#ffc107') }}">{{ $incident['severidad'] }}</td>
                <td>{{ $incident['descripcion'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Cortex AI - Reporte de auditoría generado automáticamente. Sistema Almacén.
    </div>

</body>
</html>
