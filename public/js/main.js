/**
 * main.js — Almacén Inteligente
 * Scripts globales del sistema
 */

document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // Auto-ocultar alertas después de 5 segundos
    // =========================================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        // Solo auto ocultar alertas de éxito/info, no las de error
        if (!alert.classList.contains('alert-danger')) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s ease, max-height 0.5s ease';
                alert.style.opacity = '0';
                alert.style.maxHeight = '0';
                alert.style.overflow = 'hidden';
                setTimeout(function () {
                    alert.remove();
                }, 500);
            }, 5000);
        }
    });

    // =========================================================
    // Marcar nav-link activo según la URL actual
    // =========================================================
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(function (link) {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href.split('/').pop())) {
            link.classList.add('active');
        }
    });

    // =========================================================
    // Confirmar antes de cualquier acción destructiva
    // Con class "confirm-action" en el elemento
    // =========================================================
    document.querySelectorAll('.confirm-action').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = el.dataset.confirm || '¿Confirma esta acción?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // =========================================================
    // Deshabilitar botón submit tras primer click (evitar doble envío)
    // =========================================================
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            // Si el submit fue cancelado (por ejemplo, confirm retornó false), no hacer nada
            if (e.defaultPrevented) {
                return;
            }
            
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                setTimeout(function () {
                    // Si el submit fue cancelado durante el confirm() síncrono, salimos
                    if (e.defaultPrevented) return;
                    
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';
                }, 50);
            }
        });
    });

    // =========================================================
    // Sidebar: marcar activo con comparación más precisa
    // =========================================================
    const segments = currentPath.split('/').filter(Boolean);
    const currentSection = segments.length > 1 ? segments[1] : '';

    document.querySelectorAll('.sidebar-nav .nav-link').forEach(function (link) {
        const linkSegments = link.pathname ? link.pathname.split('/').filter(Boolean) : [];
        const linkSection  = linkSegments.length > 1 ? linkSegments[1] : '';
        if (linkSection && linkSection === currentSection) {
            link.classList.add('active');
        }
    });
});

// =========================================================
// Funciones globales para controlar el Sidebar en móviles
// =========================================================
function toggleSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const icon = document.getElementById('hamburger-icon');

    if (sidebar && overlay) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Cambiar icono entre barra y cruz
        if (icon) {
            if (sidebar.classList.contains('active')) {
                icon.className = 'fa-solid fa-xmark';
            } else {
                icon.className = 'fa-solid fa-bars';
            }
        }
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const icon = document.getElementById('hamburger-icon');

    if (sidebar && overlay) {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        if (icon) {
            icon.className = 'fa-solid fa-bars';
        }
    }
}

