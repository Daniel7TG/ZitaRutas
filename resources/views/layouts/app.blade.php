<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZitaRutas - Zitácuaro</title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#10b981">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ZitaRutas">
    <meta name="description"
        content="Sigue las combis en vivo. Conoce en tiempo real cuándo llegará tu transporte en Zitácuaro, Michoacán.">

    <!-- PWA Icons para iOS -->
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/icons/icon-512x512.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">

    <!-- Google Fonts (Outfit & Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons & FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Leaflet.js CSS (Mapeo Interactivo) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Custom Premium Styles (Dark + Light Mode) -->
    <style>
        /* ═══════════════════════════════════════════════
           DARK MODE (default)
           ═══════════════════════════════════════════════ */
        :root {
            --tr-bg-main: #080c14;
            --tr-bg-body: #030712;
            --tr-bg-card: rgba(21, 28, 43, 0.75);
            --tr-bg-card-solid: #151c2b;
            --tr-bg-card-hover: rgba(255, 255, 255, 0.15);
            --tr-bg-nav: rgba(13, 17, 26, 0.9);
            --tr-bg-panel: rgba(13, 17, 26, 0.95);
            --tr-bg-search: rgba(13, 17, 26, 0.85);
            --tr-bg-input: rgba(255, 255, 255, 0.05);
            --tr-bg-btn-secondary: rgba(255, 255, 255, 0.05);
            --tr-bg-btn-secondary-hover: rgba(255, 255, 255, 0.1);
            --tr-bg-switch: rgba(255, 255, 255, 0.1);
            --tr-border: rgba(255, 255, 255, 0.08);
            --tr-border-hover: rgba(255, 255, 255, 0.15);
            --tr-border-switch: rgba(255, 255, 255, 0.15);
            --tr-green-primary: #10b981;
            --tr-green-glow: rgba(16, 185, 129, 0.25);
            --tr-text-primary: #f8fafc;
            --tr-text-muted: #94a3b8;
            --tr-text-placeholder: #64748b;
            --tr-shadow-card: 0 4px 15px rgba(0, 0, 0, 0.3);
            --tr-shadow-badge: 0 4px 10px rgba(0, 0, 0, 0.25);
            --tr-scrollbar-thumb: rgba(255, 255, 255, 0.15);
            --tr-scrollbar-thumb-list: rgba(255, 255, 255, 0.1);
            --tr-leaflet-bar-bg: #151c2b;
            --tr-leaflet-bar-hover: #1e293b;
            --tr-leaflet-attribution-bg: rgba(13, 17, 26, 0.7);
            --tr-progress-bg: rgba(255, 255, 255, 0.1);
            --tr-icon-circle-bg: #1a1a2e;
            --tr-icon-circle-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            --tr-font-title: 'Outfit', sans-serif;
            --tr-font-body: 'Inter', sans-serif;
        }

        /* ═══════════════════════════════════════════════
           LIGHT MODE
           ═══════════════════════════════════════════════ */
        [data-theme="light"] {
            --tr-bg-main: #f5f7fa;
            --tr-bg-body: #eef1f5;
            --tr-bg-card: rgba(255, 255, 255, 0.92);
            --tr-bg-card-solid: #ffffff;
            --tr-bg-card-hover: rgba(0, 0, 0, 0.04);
            --tr-bg-nav: rgba(255, 255, 255, 0.95);
            --tr-bg-panel: rgba(255, 255, 255, 0.97);
            --tr-bg-search: rgba(255, 255, 255, 0.9);
            --tr-bg-input: rgba(0, 0, 0, 0.04);
            --tr-bg-btn-secondary: rgba(0, 0, 0, 0.04);
            --tr-bg-btn-secondary-hover: rgba(0, 0, 0, 0.08);
            --tr-bg-switch: rgba(0, 0, 0, 0.12);
            --tr-border: rgba(0, 0, 0, 0.08);
            --tr-border-hover: rgba(0, 0, 0, 0.15);
            --tr-border-switch: rgba(0, 0, 0, 0.15);
            --tr-green-primary: #059669;
            --tr-green-glow: rgba(5, 150, 105, 0.18);
            --tr-text-primary: #111827;
            --tr-text-muted: #6b7280;
            --tr-text-placeholder: #9ca3af;
            --tr-shadow-card: 0 2px 12px rgba(0, 0, 0, 0.06);
            --tr-shadow-badge: 0 2px 6px rgba(0, 0, 0, 0.1);
            --tr-scrollbar-thumb: rgba(0, 0, 0, 0.12);
            --tr-scrollbar-thumb-list: rgba(0, 0, 0, 0.08);
            --tr-leaflet-bar-bg: #ffffff;
            --tr-leaflet-bar-hover: #f3f4f6;
            --tr-leaflet-attribution-bg: rgba(255, 255, 255, 0.85);
            --tr-progress-bg: rgba(0, 0, 0, 0.08);
            --tr-icon-circle-bg: #ffffff;
            --tr-icon-circle-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        /* ── Smooth transition on theme change ─────── */
        body,
        .glass-card,
        .mobile-bottom-nav,
        .btn-dark-secondary,
        .btn-neon-green,
        .form-control-custom,
        .form-check-input-custom,
        .leaflet-bar,
        .leaflet-bar a,
        .leaflet-control-attribution {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* ── Base Body ─────────────────────────────── */
        body {
            background-color: var(--tr-bg-body);
            color: var(--tr-text-primary);
            font-family: var(--tr-font-body);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        .mobile-frame-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .mobile-app-content {
            flex: 1;
            width: 100%;
            background-color: var(--tr-bg-main);
            position: relative;
        }

        /* Scrollbar */
        .mobile-app-content::-webkit-scrollbar {
            width: 4px;
        }

        .mobile-app-content::-webkit-scrollbar-thumb {
            background-color: var(--tr-scrollbar-thumb);
            border-radius: 4px;
        }

        /* ── Glassmorphism Cards ───────────────────── */
        .glass-card {
            background: var(--tr-bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--tr-border);
            border-radius: 20px;
            color: var(--tr-text-primary);
        }

        .glass-card:hover {
            border-color: var(--tr-border-hover);
        }

        /* ── Botón principal ───────────────────────── */
        .btn-neon-green {
            background-color: var(--tr-green-primary);
            color: #ffffff;
            font-family: var(--tr-font-title);
            font-weight: 600;
            border: none;
            border-radius: 16px;
            padding: 14px 24px;
            box-shadow: 0 4px 15px var(--tr-green-glow);
        }

        .btn-neon-green:hover,
        .btn-neon-green:focus {
            background-color: #047857;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            color: #ffffff;
        }

        .btn-neon-green:active {
            transform: translateY(0);
        }

        /* ── Botones secundarios ───────────────────── */
        .btn-dark-secondary {
            background-color: var(--tr-bg-btn-secondary);
            border: 1px solid var(--tr-border);
            color: var(--tr-text-primary);
            font-family: var(--tr-font-title);
            font-weight: 500;
            border-radius: 16px;
            padding: 12px 20px;
        }

        .btn-dark-secondary:hover {
            background-color: var(--tr-bg-btn-secondary-hover);
            border-color: var(--tr-border-hover);
            color: var(--tr-text-primary);
        }

        /* ── Títulos ───────────────────────────────── */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--tr-font-title);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .text-muted-custom {
            color: var(--tr-text-muted);
        }

        /* ── Override Bootstrap text-white para light mode ── */
        [data-theme="light"] .text-white {
            color: var(--tr-text-primary) !important;
        }

        [data-theme="light"] .bg-dark {
            background-color: var(--tr-bg-card-solid) !important;
        }

        [data-theme="light"] .border-secondary {
            border-color: var(--tr-border) !important;
        }

        [data-theme="light"] .bg-secondary {
            background-color: rgba(0, 0, 0, 0.08) !important;
        }

        /* ── Barra de navegación inferior móvil ──── */
        .mobile-bottom-nav {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 72px;
            background: var(--tr-bg-nav);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid var(--tr-border);
            display: flex;
            justify-content: space-around;
            align-items: center;
            z-index: 999;
        }

        .nav-item-custom {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--tr-text-muted);
            font-size: 11px;
            font-weight: 500;
            transition: all 0.2s ease;
            position: relative;
            background: none;
            border: none;
            padding: 0;
        }

        .nav-item-custom i {
            font-size: 20px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        .nav-item-custom.active {
            color: var(--tr-green-primary);
        }

        .nav-item-custom.active i {
            transform: scale(1.1);
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
        }

        [data-theme="light"] .nav-item-custom.active i {
            text-shadow: none;
        }

        /* ── Formularios y Inputs ──────────────────── */
        .form-control-custom {
            background-color: var(--tr-bg-input) !important;
            border: 1px solid var(--tr-border) !important;
            color: var(--tr-text-primary) !important;
            border-radius: 16px !important;
            padding: 12px 16px !important;
            transition: all 0.3s ease !important;
        }

        .form-control-custom:focus {
            border-color: var(--tr-green-primary) !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
        }

        .form-control-custom::placeholder {
            color: var(--tr-text-placeholder) !important;
        }

        /* ── Switch Custom ─────────────────────────── */
        .form-check-input-custom {
            width: 3.2em !important;
            height: 1.8em !important;
            background-color: var(--tr-bg-switch) !important;
            border-color: var(--tr-border-switch) !important;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .form-check-input-custom:checked {
            background-color: var(--tr-green-primary) !important;
            border-color: var(--tr-green-primary) !important;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.3) !important;
        }

        .form-check-input-custom:focus {
            box-shadow: none !important;
        }

        /* ── Insignias de Ruta ─────────────────────── */
        .route-badge {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--tr-font-title);
            font-weight: 700;
            font-size: 14px;
            color: #ffffff;
            box-shadow: var(--tr-shadow-badge);
        }

        .badge-active {
            background-color: rgba(16, 185, 129, 0.15);
            color: var(--tr-green-primary);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* ── Leaflet Map Styling ───────────────────── */
        #leaflet-map {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 0;
            z-index: 1;
        }

        .leaflet-bar {
            border: 1px solid var(--tr-border) !important;
            box-shadow: var(--tr-shadow-card) !important;
            border-radius: 12px !important;
            overflow: hidden;
        }

        .leaflet-bar a {
            background-color: var(--tr-leaflet-bar-bg) !important;
            color: var(--tr-text-primary) !important;
            border-bottom: 1px solid var(--tr-border) !important;
            transition: all 0.2s ease;
        }

        .leaflet-bar a:hover {
            background-color: var(--tr-leaflet-bar-hover) !important;
        }

        .leaflet-control-attribution {
            background-color: var(--tr-leaflet-attribution-bg) !important;
            color: var(--tr-text-muted) !important;
            font-size: 9px !important;
        }

        /* ── Custom Map Pins ───────────────────────── */
        .custom-bus-icon {
            background: none;
            border: none;
        }

        .bus-pin-glow {
            width: 32px;
            height: 32px;
            background-color: var(--tr-green-primary);
            border: 3px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #030712;
            box-shadow: 0 0 15px var(--tr-green-primary);
            font-size: 14px;
            animation: pulse-pin 2s infinite;
        }

        .user-pin-glow {
            width: 24px;
            height: 24px;
            background-color: #3b82f6;
            border: 3px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 15px #3b82f6;
            animation: pulse-pin-blue 2s infinite;
        }

        @keyframes pulse-pin {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        @keyframes pulse-pin-blue {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }

        /* ── Utilities ─────────────────────────────── */
        .fs-7 {
            font-size: 0.85rem !important;
        }

        .fs-8 {
            font-size: 0.75rem !important;
        }

        .fs-9 {
            font-size: 0.65rem !important;
        }

        .fw-extrabold {
            font-weight: 800 !important;
        }

        .animate-pulse {
            animation: animate-pulse-kf 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes animate-pulse-kf {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Hover glow effect for route cards */
        .hover-glow {
            transition: all 0.3s ease;
        }

        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3) !important;
            transform: translateY(-1px);
        }

        [data-theme="light"] .hover-glow:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .hover-white {
            transition: color 0.2s ease;
        }

        .hover-white:hover {
            color: var(--tr-text-primary) !important;
        }

        .blur-md {
            filter: blur(12px);
        }

        /* ── Scrollbar para listas ─────────────────── */
        .d-flex.flex-column.gap-2::-webkit-scrollbar {
            width: 3px;
        }

        .d-flex.flex-column.gap-2::-webkit-scrollbar-thumb {
            background-color: var(--tr-scrollbar-thumb-list);
            border-radius: 3px;
        }

        /* ── Inline style overrides para light mode ── */
        [data-theme="light"] .glass-card[style*="background: rgba(13, 17, 26"],
        [data-theme="light"] .glass-card[style*="background: rgba(16, 185, 129"] {
            background: var(--tr-bg-card) !important;
        }

        [data-theme="light"] [style*="background-color: rgba(16, 185, 129, 0.08)"] {
            background-color: rgba(5, 150, 105, 0.06) !important;
        }

        [data-theme="light"] .progress.bg-secondary {
            background-color: var(--tr-progress-bg) !important;
        }

        /* ── Botón toggle de tema ──────────────────── */
        .theme-toggle-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 1100;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 1px solid var(--tr-border);
            background: var(--tr-bg-card-solid);
            color: var(--tr-text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--tr-shadow-card);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .theme-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .theme-toggle-btn:active {
            transform: scale(0.95);
        }

        .theme-toggle-btn .icon-sun,
        .theme-toggle-btn .icon-moon {
            position: absolute;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* Dark mode: mostrar sol (para cambiar a light) */
        .theme-toggle-btn .icon-sun {
            opacity: 1;
            transform: rotate(0deg);
        }

        .theme-toggle-btn .icon-moon {
            opacity: 0;
            transform: rotate(-90deg);
        }

        /* Light mode: mostrar luna (para cambiar a dark) */
        [data-theme="light"] .theme-toggle-btn .icon-sun {
            opacity: 0;
            transform: rotate(90deg);
        }

        [data-theme="light"] .theme-toggle-btn .icon-moon {
            opacity: 1;
            transform: rotate(0deg);
        }

        /* ── Light mode: search bar input transparent ── */
        [data-theme="light"] .form-control.bg-transparent {
            color: var(--tr-text-primary) !important;
        }

        [data-theme="light"] .form-control.bg-transparent::placeholder {
            color: var(--tr-text-placeholder) !important;
        }

        /* ── Light mode: icon circle on welcome ───── */
        [data-theme="light"] .bg-dark.rounded-circle {
            background-color: var(--tr-icon-circle-bg) !important;
            box-shadow: var(--tr-icon-circle-shadow) !important;
        }

        /* ── Draggable Bottom Sheet ────────────────── */
        .routes-bottom-sheet {
            position: absolute;
            bottom: 72px; /* Justo arriba de la barra de navegación */
            left: 0;
            width: 100%;
            background: var(--tr-bg-panel);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid var(--tr-border);
            border-radius: 24px 24px 0 0;
            z-index: 1000;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.25);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
            transform: translateY(0);
        }

        [data-theme="light"] .routes-bottom-sheet {
            box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.06);
        }

        .routes-bottom-sheet.collapsed {
            transform: translateY(calc(100% - 36px)) !important;
        }

        /* Area del drag handle */
        .drag-handle {
            width: 100%;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: grab;
            user-select: none;
            touch-action: none;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        /* 3 lineas horizontales una sobre otra */
        .drag-handle-lines {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: center;
            justify-content: center;
        }

        .drag-handle-lines span {
            display: block;
            width: 32px;
            height: 3px;
            background-color: var(--tr-text-muted);
            border-radius: 2px;
            transition: background-color 0.2s ease;
        }

        .drag-handle:hover .drag-handle-lines span {
            background-color: var(--tr-green-primary);
        }

        /* Contenedor del contenido */
        .routes-sheet-content {
            padding: 0 20px 20px 20px;
            transition: opacity 0.3s ease;
            opacity: 1;
        }

        .routes-bottom-sheet.collapsed .routes-sheet-content {
            opacity: 0;
            pointer-events: none;
        }
    </style>
    <!-- Prevenir flash: aplicar tema guardado antes del render -->
    <script>
        (function() {
            var t = localStorage.getItem('zitarutas-theme');
            if (t === 'light') document.documentElement.setAttribute('data-theme', 'light');
        })();
    </script>
</head>

<body>

    <div class="mobile-frame-container">
        <div class="mobile-app-content" id="app-viewport">
            @yield('content')
        </div>
        @yield('bottom-nav')
    </div>

    <!-- Botón Toggle Dark/Light Mode -->
    <button class="theme-toggle-btn" id="themeToggleBtn" aria-label="Cambiar tema">
        <i class="fa-solid fa-sun icon-sun"></i>
        <i class="fa-solid fa-moon icon-moon"></i>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Leaflet.js JS (Mapeo Interactivo) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Axios Client HTTP para asincronía -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @stack('scripts')

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('✅ ZitaRutas SW registrado con scope:', registration.scope);
                    })
                    .catch(function(error) {
                        console.warn('⚠️ ZitaRutas SW error de registro:', error);
                    });
            });
        }
    </script>

    <!-- Theme Toggle Manager -->
    <script>
        (function() {
            const btn = document.getElementById('themeToggleBtn');
            const metaThemeColor = document.querySelector('meta[name="theme-color"]');

            function getCurrentTheme() {
                return document.documentElement.getAttribute('data-theme') || 'dark';
            }

            function applyTheme(theme) {
                if (theme === 'light') {
                    document.documentElement.setAttribute('data-theme', 'light');
                    if (metaThemeColor) metaThemeColor.content = '#f5f7fa';
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    if (metaThemeColor) metaThemeColor.content = '#10b981';
                }
                localStorage.setItem('zitarutas-theme', theme);
                // Dispatch evento para que el mapa pueda reaccionar
                document.dispatchEvent(new CustomEvent('themeChanged', {
                    detail: {
                        theme: theme
                    }
                }));
            }

            btn.addEventListener('click', function() {
                const next = getCurrentTheme() === 'dark' ? 'light' : 'dark';
                applyTheme(next);
            });
        })();
    </script>
</body>

</html>
