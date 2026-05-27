<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Mueblify') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <style>
            :root {
                color-scheme: light;
                --indigo-50: #eef2ff;
                --indigo-100: #e0e7ff;
                --indigo-200: #c7d2fe;
                --indigo-500: #6366f1;
                --indigo-600: #4f46e5;
                --indigo-700: #4338ca;
                --indigo-800: #3730a3;
                --slate-50: #f8fafc;
                --slate-100: #f1f5f9;
                --slate-500: #64748b;
                --slate-700: #334155;
                --slate-900: #0f172a;
                --white: #ffffff;
            }

            * {
                box-sizing: border-box;
            }

            body {
                min-height: 100vh;
                margin: 0;
                font-family: Figtree, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                color: var(--slate-900);
                background:
                    radial-gradient(circle at top left, rgba(99, 102, 241, 0.22), transparent 32rem),
                    linear-gradient(135deg, #f8fafc 0%, #eef2ff 52%, #ffffff 100%);
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            .page {
                width: min(1160px, calc(100% - 32px));
                margin: 0 auto;
                padding: 28px 0 48px;
            }

            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 56px;
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                font-weight: 800;
                letter-spacing: -0.03em;
                color: var(--indigo-800);
            }

            .brand-mark {
                display: grid;
                width: 42px;
                height: 42px;
                place-items: center;
                border-radius: 14px;
                color: var(--white);
                background: linear-gradient(135deg, var(--indigo-600), var(--indigo-800));
                box-shadow: 0 18px 40px rgba(67, 56, 202, 0.24);
            }

            .nav-actions {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 10px;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 42px;
                padding: 0 18px;
                border-radius: 999px;
                border: 1px solid transparent;
                font-size: 14px;
                font-weight: 700;
                transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background 160ms ease;
            }

            .button:hover {
                transform: translateY(-1px);
            }

            .button-primary {
                color: var(--white);
                background: var(--indigo-700);
                box-shadow: 0 16px 32px rgba(67, 56, 202, 0.24);
            }

            .button-primary:hover {
                background: var(--indigo-800);
            }

            .button-secondary {
                color: var(--indigo-700);
                background: rgba(255, 255, 255, 0.78);
                border-color: var(--indigo-200);
            }

            .button-secondary:hover {
                border-color: var(--indigo-500);
                box-shadow: 0 12px 28px rgba(79, 70, 229, 0.12);
            }

            .hero {
                display: grid;
                grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
                gap: 34px;
                align-items: center;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 18px;
                padding: 8px 13px;
                border: 1px solid var(--indigo-200);
                border-radius: 999px;
                color: var(--indigo-700);
                background: rgba(255, 255, 255, 0.74);
                font-size: 13px;
                font-weight: 700;
            }

            .eyebrow-dot {
                width: 8px;
                height: 8px;
                border-radius: 999px;
                background: var(--indigo-600);
                box-shadow: 0 0 0 6px rgba(99, 102, 241, 0.14);
            }

            h1 {
                max-width: 760px;
                margin: 0;
                font-size: clamp(42px, 7vw, 78px);
                line-height: 0.95;
                letter-spacing: -0.07em;
            }

            .hero-copy {
                max-width: 660px;
                margin: 24px 0 0;
                color: var(--slate-700);
                font-size: clamp(17px, 2vw, 20px);
                line-height: 1.7;
            }

            .hero-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 32px;
            }

            .hero-card {
                position: relative;
                overflow: hidden;
                padding: 26px;
                border: 1px solid rgba(199, 210, 254, 0.86);
                border-radius: 32px;
                background: rgba(255, 255, 255, 0.82);
                box-shadow: 0 24px 70px rgba(67, 56, 202, 0.12);
                backdrop-filter: blur(16px);
            }

            .hero-card::before {
                position: absolute;
                inset: -80px -80px auto auto;
                width: 190px;
                height: 190px;
                content: "";
                border-radius: 999px;
                background: rgba(99, 102, 241, 0.16);
            }

            .panel-title {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 22px;
            }

            .panel-title h2 {
                margin: 0;
                color: var(--indigo-800);
                font-size: 20px;
                letter-spacing: -0.03em;
            }

            .status-pill {
                padding: 7px 10px;
                border-radius: 999px;
                color: var(--indigo-700);
                background: var(--indigo-50);
                font-size: 12px;
                font-weight: 800;
            }

            .mini-table {
                position: relative;
                display: grid;
                gap: 12px;
            }

            .mini-row {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 14px;
                align-items: center;
                padding: 14px;
                border: 1px solid var(--slate-100);
                border-radius: 18px;
                background: var(--white);
            }

            .mini-row strong {
                display: block;
                margin-bottom: 4px;
                color: var(--slate-900);
                font-size: 14px;
            }

            .mini-row span {
                color: var(--slate-500);
                font-size: 13px;
            }

            .tag {
                padding: 7px 10px;
                border-radius: 999px;
                color: var(--indigo-700);
                background: var(--indigo-50);
                font-size: 12px;
                font-weight: 800;
                white-space: nowrap;
            }

            .modules {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
                margin-top: 34px;
            }

            .module-card {
                padding: 22px;
                border: 1px solid rgba(199, 210, 254, 0.78);
                border-radius: 24px;
                background: rgba(255, 255, 255, 0.78);
                box-shadow: 0 18px 42px rgba(67, 56, 202, 0.08);
            }

            .module-icon {
                display: grid;
                width: 42px;
                height: 42px;
                margin-bottom: 16px;
                place-items: center;
                border-radius: 14px;
                color: var(--indigo-700);
                background: var(--indigo-50);
                font-weight: 900;
            }

            .module-card h3 {
                margin: 0 0 8px;
                font-size: 17px;
                letter-spacing: -0.03em;
            }

            .module-card p {
                margin: 0;
                color: var(--slate-500);
                font-size: 14px;
                line-height: 1.6;
            }

            .footer-note {
                margin-top: 32px;
                color: var(--slate-500);
                font-size: 13px;
                text-align: center;
            }

            @media (max-width: 860px) {
                .topbar {
                    align-items: flex-start;
                    flex-direction: column;
                    margin-bottom: 36px;
                }

                .nav-actions {
                    justify-content: flex-start;
                    width: 100%;
                }

                .hero {
                    grid-template-columns: 1fr;
                }

                .modules {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 520px) {
                .page {
                    width: min(100% - 24px, 1160px);
                    padding-top: 18px;
                }

                .button {
                    width: 100%;
                }

                .hero-card {
                    padding: 18px;
                    border-radius: 24px;
                }

                .mini-row {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <header class="topbar">
                <a href="{{ url('/') }}" class="brand" aria-label="Inicio Mueblify">
                    <span class="brand-mark">M</span>
                    <span>Mueblify</span>
                </a>

                @if (Route::has('login'))
                    <nav class="nav-actions" aria-label="Acceso principal">
                        @auth
                            <a href="{{ route('dashboard') }}" class="button button-primary">Ir al dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="button button-secondary">Iniciar sesión</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="button button-primary">Registrarse</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main>
                <section class="hero" aria-labelledby="hero-title">
                    <div>
                        <div class="eyebrow">
                            <span class="eyebrow-dot"></span>
                            Gestión simple para clientes y catálogo
                        </div>

                        <h1 id="hero-title">Administra tu negocio de muebles con una interfaz clara.</h1>

                        <p class="hero-copy">
                            Centraliza clientes, productos y futuras cotizaciones en un flujo ordenado. Esta pantalla de inicio usa colores claros y el índigo del proyecto para mantener una experiencia limpia y consistente.
                        </p>

                        <div class="hero-actions">
                            @auth
                                <a href="{{ route('clientes.index') }}" class="button button-primary">Ver clientes</a>
                                <a href="{{ route('catalogo.index') }}" class="button button-secondary">Ver catálogo</a>
                            @else
                                <a href="{{ route('login') }}" class="button button-primary">Entrar al sistema</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="button button-secondary">Crear una cuenta</a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <aside class="hero-card" aria-label="Vista previa del sistema">
                        <div class="panel-title">
                            <h2>Resumen del día</h2>
                            <span class="status-pill">Vista previa</span>
                        </div>

                        <div class="mini-table">
                            <div class="mini-row">
                                <div>
                                    <strong>Empresa Mueblera S.A.</strong>
                                    <span>Cliente activo con datos de contacto</span>
                                </div>
                                <span class="tag">Cliente</span>
                            </div>

                            <div class="mini-row">
                                <div>
                                    <strong>Mesa de comedor</strong>
                                    <span>SKU-001 · Madera · Stock 10</span>
                                </div>
                                <span class="tag">Catálogo</span>
                            </div>

                            <div class="mini-row">
                                <div>
                                    <strong>Cotizaciones</strong>
                                    <span>Preparado para el siguiente módulo</span>
                                </div>
                                <span class="tag">Próximo</span>
                            </div>
                        </div>
                    </aside>
                </section>

                <section class="modules" aria-label="Módulos principales">
                    <article class="module-card">
                        <div class="module-icon">01</div>
                        <h3>Clientes</h3>
                        <p>Lista, registra, edita y elimina clientes con campos como RFC, teléfono y dirección.</p>
                    </article>

                    <article class="module-card">
                        <div class="module-icon">02</div>
                        <h3>Catálogo</h3>
                        <p>Organiza productos por SKU, material, precio, stock y estado activo.</p>
                    </article>

                    <article class="module-card">
                        <div class="module-icon">03</div>
                        <h3>Flujo preparado</h3>
                        <p>La interfaz queda lista para conectar modelos, migraciones y cotizaciones más adelante.</p>
                    </article>
                </section>
            </main>

            <p class="footer-note">Interfaz de inicio para Mueblify · Laravel Breeze</p>
        </div>
    </body>
</html>
