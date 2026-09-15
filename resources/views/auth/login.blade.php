<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Almacén Inteligente</title>
    <meta name="description" content="Sistema de gestión de almacén. Inicia sesión para continuar.">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0F172A;
            overflow-x: hidden;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 15% 15%, rgba(2, 132, 199, 0.15) 0%, transparent 45%),
                        radial-gradient(circle at 85% 85%, rgba(13, 148, 136, 0.15) 0%, transparent 45%),
                        linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            position: relative;
            padding: 1.5rem;
            box-sizing: border-radius;
        }

        /* Card Contenedora */
        .auth-box {
            width: 100%;
            max-width: 430px;
            background: #FFFFFF;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            position: relative;
            z-index: 10;
            animation: loginEntrance 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes loginEntrance {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Barra Superior Accent */
        .auth-top-accent {
            height: 5px;
            background: linear-gradient(90deg, #0284C7, #0D9488, #6366F1);
        }

        .auth-body {
            padding: 2.5rem 2.25rem 2rem;
        }

        /* Logo e Icono */
        .brand-avatar {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #0284C7, #0D9488);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            color: #FFFFFF;
            font-size: 1.75rem;
            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.3);
        }

        .auth-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            color: #0F172A;
            margin: 0 0 0.4rem 0;
            letter-spacing: -0.5px;
        }

        .auth-subtitle {
            text-align: center;
            font-size: 0.85rem;
            color: #64748B;
            margin: 0 0 2rem 0;
            font-weight: 500;
        }

        /* Inputs */
        .field-group {
            margin-bottom: 1.4rem;
        }

        .field-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .field-wrapper {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 1rem;
            transition: color 0.2s ease;
        }

        .custom-input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.75rem;
            border: 2px solid #E2E8F0;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            color: #0F172A;
            background: #F8FAFC;
            transition: all 0.25s ease;
            box-sizing: border-box;
        }

        .custom-input:focus {
            outline: none;
            border-color: #0284C7;
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
        }

        .custom-input:focus + .field-icon {
            color: #0284C7;
        }

        /* Botón Submit */
        .btn-submit-login {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #0284C7 0%, #0D9488 100%);
            color: #FFFFFF;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.3);
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }

        .btn-submit-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(2, 132, 199, 0.4);
            background: linear-gradient(135deg, #0369A1 0%, #0F766E 100%);
        }

        .btn-submit-login:active {
            transform: translateY(0);
        }

        .auth-footer-text {
            text-align: center;
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px solid #F1F5F9;
            font-size: 0.78rem;
            color: #64748B;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }

        /* Alertas */
        .auth-alert {
            padding: 0.85rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .auth-alert-danger {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .auth-alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-box">
            <div class="auth-top-accent"></div>
            <div class="auth-body">
                <div class="brand-avatar">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <h1 class="auth-title">Almacén Inteligente</h1>
                <p class="auth-subtitle">Sistema de Control y Logística de Inventario</p>

                @if ($errors->any())
                    <div class="auth-alert auth-alert-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="auth-alert auth-alert-success">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" autocomplete="off">
                    @csrf

                    <div class="field-group">
                        <label class="field-label" for="usuario">Usuario</label>
                        <div class="field-wrapper">
                            <input type="text"
                                   id="usuario"
                                   name="usuario"
                                   class="custom-input"
                                   placeholder="Ingresa tu usuario"
                                   required
                                   autofocus
                                   autocomplete="new-password"
                                   readonly
                                   onfocus="this.removeAttribute('readonly');"
                                   value="{{ old('usuario') }}">
                            <i class="fa-solid fa-user field-icon"></i>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="password">Contraseña</label>
                        <div class="field-wrapper">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="custom-input"
                                   placeholder="••••••••"
                                   required
                                   autocomplete="new-password"
                                   readonly
                                   onfocus="this.removeAttribute('readonly');">
                            <i class="fa-solid fa-lock field-icon"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-login" id="btn-login">
                        <span>Iniciar Sesión</span>
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </button>
                </form>

                <div class="auth-footer-text">
                    <i class="fa-solid fa-shield-halved" style="color: #0284C7;"></i>
                    <span>Acceso Restringido · Personal Autorizado</span>
                </div>

                {{-- Botón de emergencia Cortex --}}
                @if ($errors->any() || session('error'))
                <div style="
                    margin-top: 1.25rem;
                    padding: 0.85rem 1rem;
                    border-radius: 12px;
                    background: #F0F9FF;
                    border: 1px solid #BAE6FD;
                    text-align: center;
                ">
                    <p style="font-size: 0.78rem; color: #0369A1; margin: 0 0 0.5rem 0; font-weight: 600;">
                        ¿Problemas para acceder al sistema?
                    </p>
                    <a href="{{ route('cortex.rescue') }}" style="
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        color: #0284C7;
                        font-size: 0.8rem;
                        font-weight: 700;
                        text-decoration: none;
                        padding: 0.45rem 0.9rem;
                        border-radius: 8px;
                        border: 1px solid #0284C7;
                        background: #FFFFFF;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                        transition: all 0.2s;
                    " onmouseover="this.style.background='#0284C7'; this.style.color='#FFFFFF'"
                       onmouseout="this.style.background='#FFFFFF'; this.style.color='#0284C7'">
                        <i class="fa-solid fa-shield-heart"></i>
                        Consola de Rescate Cortex
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
