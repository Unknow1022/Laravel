<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cortex - Consola de Rescate</title>
    <!-- Fuentes y iconos (Offline) -->
    <style>
        @font-face {
            font-family: 'Inter';
            src: url('/fonts/inter/inter-400.woff2') format('woff2');
            font-weight: 400; font-display: swap;
        }
        @font-face {
            font-family: 'Inter';
            src: url('/fonts/inter/inter-600.woff2') format('woff2');
            font-weight: 600; font-display: swap;
        }
        @font-face {
            font-family: 'Inter';
            src: url('/fonts/inter/inter-800.woff2') format('woff2');
            font-weight: 800; font-display: swap;
        }
        @font-face {
            font-family: 'JetBrains Mono';
            src: url('/fonts/jetbrains-mono/jetbrains-mono-400.woff2') format('woff2');
            font-weight: 400; font-display: swap;
        }
        @font-face {
            font-family: 'JetBrains Mono';
            src: url('/fonts/jetbrains-mono/jetbrains-mono-700.woff2') format('woff2');
            font-weight: 700; font-display: swap;
        }
    </style>
    <link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #060b13;
            --card-bg: rgba(17, 34, 64, 0.4);
            --border-color: rgba(100, 255, 218, 0.15);
            --primary: #64ffda;
            --primary-glow: rgba(100, 255, 218, 0.3);
            --danger: #f43f5e;
            --danger-glow: rgba(244, 63, 94, 0.3);
            --warning: #eab308;
            --warning-glow: rgba(234, 179, 8, 0.3);
            --success: #10b981;
            --text-main: #ccd6f6;
            --text-muted: #8892b0;
            --console-bg: #03070c;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(100, 255, 218, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(244, 63, 94, 0.03) 0px, transparent 50%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        header {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto 2rem auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 1.5rem;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-icon {
            font-size: 2.2rem;
            color: var(--primary);
            text-shadow: 0 0 10px var(--primary-glow);
            animation: pulse-glow 3s infinite alternate;
        }

        .logo-text h1 {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 2px;
            color: #fff;
        }

        .logo-text span {
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .badge-emergency {
            background: rgba(244, 63, 94, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 63, 94, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 0 15px rgba(244, 63, 94, 0.1);
        }

        .badge-emergency i {
            animation: blink 1.5s infinite;
        }

        main {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            flex-grow: 1;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 900px) {
            main {
                grid-template-columns: 3fr 2fr;
            }
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #fff;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.2rem;
        }

        @media (min-width: 600px) {
            .card-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .glass-card:hover {
            transform: translateY(-4px);
            border-color: rgba(100, 255, 218, 0.3);
            box-shadow: 0 12px 40px 0 rgba(100, 255, 218, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .card-icon {
            font-size: 1.5rem;
            color: var(--text-muted);
        }

        .status-pill {
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pass {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-fail {
            background: rgba(244, 63, 94, 0.1);
            color: var(--danger);
            border: 1px solid rgba(244, 63, 94, 0.3);
            animation: pulse-border-red 2s infinite;
        }

        .status-warning {
            background: rgba(234, 179, 8, 0.1);
            color: var(--warning);
            border: 1px solid rgba(234, 179, 8, 0.3);
        }

        .card-body h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 0.5rem;
        }

        .card-body p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.4;
            margin-bottom: 1.5rem;
        }

        .btn-repair {
            background: rgba(100, 255, 218, 0.05);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .btn-repair:hover:not(:disabled) {
            background: var(--primary);
            color: var(--bg-color);
            box-shadow: 0 0 15px var(--primary-glow);
        }

        .btn-repair:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            border-color: var(--text-muted);
            color: var(--text-muted);
        }

        .control-panel {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .btn-global-repair {
            background: linear-gradient(135deg, #64ffda 0%, #10b981 100%);
            border: none;
            color: var(--bg-color);
            padding: 1.2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(100, 255, 218, 0.2);
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
        }

        .btn-global-repair:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(100, 255, 218, 0.4);
        }

        .btn-global-repair:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-global-repair:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: var(--text-muted);
        }

        .terminal {
            background: var(--console-bg);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.5rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.8);
            display: flex;
            flex-direction: column;
            height: 380px;
        }

        .terminal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 0.8rem;
            margin-bottom: 1rem;
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        .terminal-dots {
            display: flex;
            gap: 6px;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .dot-red { background: #f43f5e; }
        .dot-yellow { background: #eab308; }
        .dot-green { background: #10b981; }

        .terminal-body {
            overflow-y: auto;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
        }

        .terminal-body::-webkit-scrollbar {
            width: 6px;
        }

        .terminal-body::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .log-line {
            line-height: 1.5;
            word-break: break-all;
        }

        .log-time {
            color: var(--text-muted);
        }

        .log-type {
            font-weight: bold;
        }

        .log-info { color: #3b82f6; }
        .log-success { color: var(--success); }
        .log-error { color: var(--danger); }
        .log-warning { color: var(--warning); }
        .log-sys { color: var(--primary); }

        .link-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
            margin-top: 1rem;
            align-self: center;
        }

        .link-back:hover {
            color: var(--primary);
        }

        footer {
            max-width: 1200px;
            width: 100%;
            margin: 2rem auto 0 auto;
            text-align: center;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            color: var(--text-muted);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 1.5rem;
        }

        /* Animations */
        @keyframes pulse-glow {
            0% { text-shadow: 0 0 5px var(--primary-glow); }
            100% { text-shadow: 0 0 15px var(--primary); }
        }

        @keyframes pulse-border-red {
            0% { border-color: rgba(244, 63, 94, 0.3); box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.2); }
            70% { border-color: rgba(244, 63, 94, 0.7); box-shadow: 0 0 0 8px rgba(244, 63, 94, 0); }
            100% { border-color: rgba(244, 63, 94, 0.3); box-shadow: 0 0 0 0 rgba(244, 63, 94, 0); }
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* Loading Spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <header>
        <div class="logo-area">
            <div class="logo-icon">
                <i class="fa-solid fa-shield-heart"></i>
            </div>
            <div class="logo-text">
                <h1>CORTEX</h1>
                <span>SISTEMA DE AUTOREPARACIÓN</span>
            </div>
        </div>
        <div class="badge-emergency">
            <i class="fa-solid fa-triangle-exclamation"></i> CONSOLA DE RESCATE ACTIVA
        </div>
    </header>

    <main>
        <!-- Columna Izquierda: Diagnóstico e Instrumentos -->
        <div>
            <div class="section-title">
                <i class="fa-solid fa-gauge-high"></i> Estado del Núcleo del Almacén
            </div>
            
            <div class="card-grid">
                
                <!-- Tarjeta 1: Base de Datos -->
                <div class="glass-card" id="card-database">
                    <div class="card-header">
                        <div class="card-icon"><i class="fa-solid fa-database"></i></div>
                        <span class="status-pill {{ $diagnostic['database']['status'] === 'PASS' ? 'status-pass' : ($diagnostic['database']['status'] === 'WARNING' ? 'status-warning' : 'status-fail') }}" id="status-database">
                            {{ $diagnostic['database']['status'] }}
                        </span>
                    </div>
                    <div class="card-body">
                        <h3>Base de Datos</h3>
                        <p id="msg-database">{{ $diagnostic['database']['message'] }}</p>
                        <button class="btn-repair" onclick="runSingleRepair('database')" id="btn-database">
                            <i class="fa-solid fa-wrench"></i> Reparar Conexión/Tablas
                        </button>
                    </div>
                </div>

                <!-- Tarjeta 2: Modelos ORM -->
                <div class="glass-card" id="card-models">
                    <div class="card-header">
                        <div class="card-icon"><i class="fa-solid fa-diagram-project"></i></div>
                        <span class="status-pill {{ $diagnostic['models']['status'] === 'PASS' ? 'status-pass' : ($diagnostic['models']['status'] === 'WARNING' ? 'status-warning' : 'status-fail') }}" id="status-models">
                            {{ $diagnostic['models']['status'] }}
                        </span>
                    </div>
                    <div class="card-body">
                        <h3>Modelos e Integridad</h3>
                        <p id="msg-models">{{ $diagnostic['models']['message'] }}</p>
                        <button class="btn-repair" onclick="runSingleRepair('models')" id="btn-models">
                            <i class="fa-solid fa-wrench"></i> Saneamiento de Datos
                        </button>
                    </div>
                </div>

                <!-- Tarjeta 3: Permisos y Almacenamiento -->
                <div class="glass-card" id="card-storage">
                    <div class="card-header">
                        <div class="card-icon"><i class="fa-solid fa-folder-open"></i></div>
                        <span class="status-pill {{ $diagnostic['storage']['status'] === 'PASS' ? 'status-pass' : ($diagnostic['storage']['status'] === 'WARNING' ? 'status-warning' : 'status-fail') }}" id="status-storage">
                            {{ $diagnostic['storage']['status'] }}
                        </span>
                    </div>
                    <div class="card-body">
                        <h3>Almacenamiento (Logs/Cache)</h3>
                        <p id="msg-storage">{{ $diagnostic['storage']['message'] }}</p>
                        <button class="btn-repair" onclick="runSingleRepair('storage')" id="btn-storage">
                            <i class="fa-solid fa-wrench"></i> Limpiar Caché / Permisos
                        </button>
                    </div>
                </div>

                <!-- Tarjeta 4: Configuración y Seguridad -->
                <div class="glass-card" id="card-security">
                    <div class="card-header">
                        <div class="card-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <span class="status-pill {{ $diagnostic['security']['status'] === 'PASS' ? 'status-pass' : ($diagnostic['security']['status'] === 'WARNING' ? 'status-warning' : 'status-fail') }}" id="status-security">
                            {{ $diagnostic['security']['status'] }}
                        </span>
                    </div>
                    <div class="card-body">
                        <h3>Entorno y Seguridad</h3>
                        <p id="msg-security">{{ $diagnostic['security']['message'] }}</p>
                        <button class="btn-repair" onclick="runSingleRepair('security')" id="btn-security">
                            <i class="fa-solid fa-wrench"></i> Ajustar Políticas
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Columna Derecha: Consola y Mandos -->
        <div class="control-panel">
            <div>
                <div class="section-title">
                    <i class="fa-solid fa-screwdriver-wrench"></i> Acciones del Autopiloto
                </div>
                <button class="btn-global-repair" id="btn-global" onclick="runGlobalRepair()">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Ejecutar Reparación General
                </button>
            </div>

            <div>
                <div class="section-title">
                    <i class="fa-solid fa-terminal"></i> Consola de Auditoría (Rescate)
                </div>
                <div class="terminal">
                    <div class="terminal-header">
                        <div class="terminal-dots">
                            <span class="dot dot-red"></span>
                            <span class="dot dot-yellow"></span>
                            <span class="dot dot-green"></span>
                        </div>
                        <span>cortex-rescue@localhost:~</span>
                    </div>
                    <div class="terminal-body" id="console-logs">
                        <div class="log-line">
                            <span class="log-time">[16:59:16]</span>
                            <span class="log-type log-sys">[SYS]</span>
                            <span>Consola de rescate iniciada. Esperando órdenes.</span>
                        </div>
                        <div class="log-line">
                            <span class="log-time">[16:59:17]</span>
                            <span class="log-type log-info">[INFO]</span>
                            <span>Diagnóstico inicial cargado. Origen de IP verificado: LOCALHOST.</span>
                        </div>
                    </div>
                </div>
            </div>

            <a href="/dashboard" class="link-back">
                <i class="fa-solid fa-arrow-left"></i> Volver al Portal de Herramientas
            </a>
        </div>
    </main>

    <footer>
        Cortex Diagnostics Console v2.0 - Laravel 11 Autonomous Self-Healing Engine
    </footer>

    <script>
        const csrfToken = "{{ csrf_token() }}";

        function printLog(msg, type = 'info') {
            const consoleLogs = document.getElementById('console-logs');
            const now = new Date();
            const timeStr = now.toTimeString().split(' ')[0];
            
            const line = document.createElement('div');
            line.className = 'log-line';
            
            let badgeClass = 'log-info';
            let badgeText = '[INFO]';
            
            if (type === 'success') { badgeClass = 'log-success'; badgeText = '[OK]'; }
            else if (type === 'error') { badgeClass = 'log-error'; badgeText = '[FAIL]'; }
            else if (type === 'warning') { badgeClass = 'log-warning'; badgeText = '[WARN]'; }
            else if (type === 'sys') { badgeClass = 'log-sys'; badgeText = '[SYS]'; }

            line.innerHTML = `
                <span class="log-time">[${timeStr}]</span>
                <span class="log-type ${badgeClass}">${badgeText}</span>
                <span>${msg}</span>
            `;
            
            consoleLogs.appendChild(line);
            consoleLogs.scrollTop = consoleLogs.scrollHeight;
        }

        async function runSingleRepair(component) {
            const btn = document.getElementById(`btn-${component}`);
            const originalContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner spinner"></i> Procesando...`;
            
            printLog(`Iniciando protocolo de reparación para: ${component.toUpperCase()}...`, 'sys');
            
            try {
                const response = await fetch(`/cortex/rescue/repair/${component}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    printLog(data.message, 'success');
                    // Actualizar UI localmente a PASS
                    updateComponentStatus(component, 'PASS', 'Reparación aplicada con éxito.');
                } else {
                    printLog(`Error al reparar ${component}: ${data.message}`, 'error');
                }
            } catch (err) {
                printLog(`Fallo crítico de comunicación con el núcleo: ${err.message}`, 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }

        async function runGlobalRepair() {
            const btn = document.getElementById('btn-global');
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner spinner"></i> Sanando Sistema Autónomamente...`;
            
            printLog("Iniciando Protocolo de Saneamiento Global Autónomo...", 'sys');
            
            const components = ['database', 'storage', 'security', 'models'];
            
            for (const comp of components) {
                await runSingleRepair(comp);
            }
            
            printLog("Saneamiento global finalizado. Comprobando estado actual...", 'sys');
            
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-wand-magic-sparkles"></i> Ejecutar Reparación General`;
            
            // Recargar para actualizar todos los diagnósticos reales
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        }

        function updateComponentStatus(component, status, message) {
            const pill = document.getElementById(`status-${component}`);
            const msgText = document.getElementById(`msg-${component}`);
            
            pill.className = `status-pill status-${status.toLowerCase()}`;
            pill.innerText = status;
            msgText.innerText = message;
        }
    </script>
</body>
</html>
