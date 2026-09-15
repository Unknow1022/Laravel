import http from 'k6/http';
import { sleep, check } from 'k6';

// Configuración de la prueba de carga y estrés en k6
export const options = {
    // Definimos varios escenarios/etapas de carga progresiva
    stages: [
        // 1. Fase de Calentamiento / Línea Base (Normal Load)
        { duration: '30s', target: 20 },  // Sube de 0 a 20 usuarios virtuales (VUs)
        { duration: '1m', target: 20 },   // Se mantiene en 20 VUs para establecer la Línea Base

        // 2. Fase de Estrés / Carga Máxima (Soportable en Local)
        { duration: '45s', target: 100 }, // Sube de 20 a 100 VUs (Pico máximo recomendado)
        { duration: '1m', target: 100 },  // Mantiene 100 VUs para medir estabilidad de la máquina

        // 3. Fase de Recuperación / Resiliencia (Descenso)
        { duration: '30s', target: 0 },   // Baja a 0 VUs para medir liberación de recursos
    ],
    // Definir umbrales de éxito (Thresholds) para evaluar la calidad del servicio
    thresholds: {
        http_req_failed: ['rate<0.05'], // Menos del 5% de errores
        http_req_duration: ['p(95)<2000'], // El 95% de las peticiones deben tardar menos de 2s
    },
};

export default function () {
    // URL base de la aplicación (se puede sobrescribir con k6 run -e BASE_URL=http://...)
    const baseUrl = __ENV.BASE_URL || 'http://localhost';

    // ==========================================
    // ESCENARIO 1: Obtener la página de Login y extraer CSRF token
    // ==========================================
    const getRes = http.get(`${baseUrl}/login`);
    
    const pageLoaded = check(getRes, {
        'Login: Página carga correctamente (200)': (r) => r.status === 200,
    });

    if (!pageLoaded) {
        console.error(`Fallo al cargar la página de login. HTTP Status: ${getRes.status}`);
        sleep(1);
        return;
    }

    // Extraer el token CSRF para poder hacer el login POST
    const csrfToken = getRes.html().find('input[name="_token"]').attr('value');

    if (!csrfToken) {
        console.warn('Advertencia: No se pudo extraer el token CSRF de la página.');
        sleep(1);
        return;
    }

    // ==========================================
    // ESCENARIO 2: Iniciar Sesión (Bcrypt es intensivo en CPU)
    // ==========================================
    const loginPayload = {
        _token: csrfToken,
        usuario: 'admin',
        password: 'Adm!n#2026$SecureX9',
    };

    const loginRes = http.post(`${baseUrl}/login`, loginPayload, {
        redirects: 0, // No seguir el redireccionamiento para capturar la cookie de sesión en esta petición
    });

    const isLoggedIn = check(loginRes, {
        'Login: Redirección post-auth exitosa (302)': (r) => r.status === 302,
    });

    if (!isLoggedIn) {
        console.error(`Login fallido. Código HTTP: ${loginRes.status}`);
        sleep(1);
        return;
    }

    // ==========================================
    // ESCENARIO 3: Carga del Dashboard (Múltiples consultas DB)
    // ==========================================
    const dashboardRes = http.get(`${baseUrl}/dashboard`);
    check(dashboardRes, {
        'Dashboard: Carga de panel principal exitosa (200)': (r) => r.status === 200,
    });
    sleep(1); // Tiempo de lectura del usuario (simulado)

    // ==========================================
    // ESCENARIO 4: Centro de Control Cortex AI (Análisis y Auditoría)
    // ==========================================
    const cortexRes = http.get(`${baseUrl}/cortex`);
    check(cortexRes, {
        'Cortex: Panel de inteligencia AI carga exitosa (200)': (r) => r.status === 200,
    });
    sleep(1.5);

    // ==========================================
    // ESCENARIO 5: Escaneo de diagnóstico Cortex (Alta carga de CPU e inserción de logs en DB)
    // ==========================================
    const scanRes = http.get(`${baseUrl}/cortex/scan`);
    check(scanRes, {
        'Cortex: Escaneo de salud completado (302/200)': (r) => r.status === 302 || r.status === 200,
    });
    sleep(2);

    // ==========================================
    // ESCENARIO 6: Exportación de Reporte PDF (El cuello de botella crítico: DomPDF)
    // Simula a un porcentaje menor de usuarios (15%) descargando reportes concurrentemente
    // ==========================================
    if (Math.random() < 0.15) {
        const pdfRes = http.get(`${baseUrl}/cortex/export-audit`);
        check(pdfRes, {
            'Reportes: Exportación de PDF de auditoría exitoso (200)': (r) => r.status === 200,
            'Reportes: El tipo de contenido es PDF': (r) => r.headers['Content-Type'] === 'application/pdf',
        });
        sleep(3);
    }

    sleep(1); // Espera final de iteración
}
