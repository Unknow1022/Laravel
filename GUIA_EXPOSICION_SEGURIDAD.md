# Guía de Exposición: Seguridad y Prevención de Vulnerabilidades
Esta guía describe paso a paso cómo realizar la demostración en vivo ante el docente para obtener el puntaje completo (4 puntos) en la rúbrica de **Prevención de vulnerabilidades básicas**.

Todo se realiza directamente desde el sistema, sin necesidad de consultar base de datos ni usar terminales externas.

---

## 🧭 Introducción Técnica (Lo que debes decir)
> *"Profesor, para esta entrega hemos implementado un **Entorno Sandbox de Seguridad** en nuestro panel Cortex NOC. Este nos permite desactivar las protecciones del sistema en tiempo real para demostrar cómo operan los ataques de **Cross-Site Scripting (XSS)** e **Inyección SQL (SQLi)**, y cómo nuestras contramedidas de código los anulan al activarlas."*

---

## 🔴 Paso 1: Demostración de Cross-Site Scripting (XSS)

El **Stored XSS** ocurre cuando un formulario acepta código de script directo y el sistema lo guarda sin filtrar, ejecutándose en el navegador del usuario que visualiza los datos.

### A) El Ataque (Seguridad Apagada)
1. Ve al menú lateral → **Cortex Assistant**.
2. En la tarjeta **Cortex Security Sandbox**, haz clic en el botón rojo **"Desactivar Filtro (Simular Vulnerabilidad)"** en la columna de XSS. Verás el indicador en rojo: `VULNERABLE`.
3. Ve a **Personal** (menú lateral) → **Registrar Personal** (o botón Nuevo).
4. Llena los datos con un script malicioso:
   * **Nombre**: `<script>alert('¡XSS Exitoso en Almacén!')</script>`
   * **Apellidos**: `Mendoza`
   * **DNI**: `99999999` (8 dígitos cualquiera)
   * **Cargo**: `Operador`
   * **Teléfono**: `987654321`
5. Haz clic en **REGISTRAR PERSONAL**.
6. **Resultado en pantalla**: Al redirigirte al listado de personal, **se ejecutará inmediatamente una ventana emergente (popup) en tu navegador** con el mensaje *"¡XSS Exitoso en Almacén!"*. Esto prueba que la vulnerabilidad fue explotada con éxito.

### B) La Defensa (Seguridad Encendida)
1. Regresa a **Cortex Assistant**.
2. En el sandbox, haz clic en el botón verde **"Activar Filtro (Bloquear Ataque)"** de la columna XSS. El indicador cambiará a verde: `ACTIVO`.
3. Ve a **Personal** → **Registrar Personal**.
4. Llena los datos con el mismo script:
   * **Nombre**: `<script>alert('Este ataque será bloqueado')</script>`
   * **DNI**: `88888888`
5. Haz clic en **REGISTRAR PERSONAL**.
6. **Resultado en pantalla**: El registro se completa sin que aparezca ninguna ventana emergente. El nombre del trabajador se muestra limpio en la tabla, debido a que nuestro middleware de sanitización interceptó la entrada y eliminó las etiquetas de script de forma segura.

---

## 🔵 Paso 2: Demostración de Inyección SQL (SQLi)

La inyección SQL ocurre cuando los parámetros ingresados por el usuario se concatenan directamente en la consulta SQL del servidor en lugar de usar enlaces de parámetros seguros (consultas preparadas).

### A) El Ataque (Seguridad Apagada)
1. Ve a **Cortex Assistant** → Sandbox de Seguridad.
2. En la columna SQLi, haz clic en el botón rojo **"Desactivar Filtro (Simular Vulnerabilidad)"**. El indicador se pondrá en rojo: `VULNERABLE`.
3. Ve a **Catálogo** (menú lateral).
4. En el buscador del catálogo, ingresa el siguiente payload de inyección lógica:
   ```sql
   ' OR 1=1 --
   ```
5. Presiona Enter o busca.
6. **Resultado en pantalla**: 
   * Acepta la inyección y el depurador **Cortex SQL Debugger** de la parte inferior muestra la consulta insegura concatenada:
     `SELECT * FROM herramientas WHERE deleted_at IS NULL AND (nombre LIKE '%' OR 1=1 --%')`
   * Esto rompe la lógica de filtrado de búsqueda devolviendo registros indiscriminadamente (inyección lógica exitosa).

### B) La Defensa (Seguridad Encendida)
1. Regresa a **Cortex Assistant**.
2. En el sandbox, haz clic en el botón verde **"Activar Filtro (Bloquear Ataque)"** de la columna SQLi. El indicador cambiará a verde: `ACTIVO`.
3. Ve a **Catálogo**.
4. Ingresa el mismo payload de ataque en el buscador:
   ```sql
   ' OR 1=1 --
   ```
5. **Resultado en pantalla**:
   * El sistema devuelve **0 resultados** y maneja la búsqueda de forma segura.
   * El **Cortex SQL Debugger** en la parte inferior muestra la consulta segura con placeholders parametrizados:
     `SELECT * FROM herramientas WHERE deleted_at IS NULL AND (nombre LIKE ? OR codigo LIKE ?)`
     `Bindings: ["%' OR 1=1 --%"]`
   * Esto demuestra que la entrada maliciosa fue tratada como un texto literal inofensivo y no como código SQL ejecutable.

---

## 🛠️ ¿Qué se implementó técnicamente en el código? (Para responder preguntas)

Si el profesor pregunta qué hicieron en el código para lograr esto:

1. **Prevención de XSS (Input Sanitization Middleware)**:
   * Creamos un middleware global en `App\Http\Middleware\XssSanitization` que intercepta las peticiones web en el pipeline.
   * El middleware recorre recursivamente todos los inputs y limpia los tags HTML/PHP ejecutando `strip_tags()` antes de que lleguen a los controladores y a la base de datos.
2. **Prevención de SQL Injection (Consultas Preparadas / Enlaces de Parámetros)**:
   * Por defecto, el ORM de Laravel (Eloquent) utiliza la librería PDO de PHP para enlazar automáticamente los parámetros (`Prepared Statements`).
   * Al compilar las queries, las entradas de los usuarios se pasan por separado de las instrucciones SQL, neutralizando comillas y comodines.
3. **Seguridad en Cookies y Sesión (`config/session.php`)**:
   * Habilitamos `http_only => true` para bloquear la lectura de la cookie de sesión desde JavaScript, anulando el robo de identidad por inyección de scripts (Session Hijacking).
   * Configuramos `same_site => 'lax'` para denegar la transmisión de la cookie de sesión en peticiones de origen externo, mitigando ataques de falsificación de petición en sitios cruzados (CSRF).
