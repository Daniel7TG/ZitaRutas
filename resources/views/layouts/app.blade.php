<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZitaRutas - Zitácuaro</title>

    <!-- Google Fonts (Outfit & Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons & FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- Leaflet.js CSS (Mapeo Interactivo) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

    <!-- Custom Premium Dark Styles -->
    <style>
        :root {
            --tr-bg-main: #080c14;
            --tr-bg-card: rgba(21, 28, 43, 0.75);
            --tr-bg-card-solid: #151c2b;
            --tr-border: rgba(255, 255, 255, 0.08);
            --tr-green-primary: #10b981;
            --tr-green-glow: rgba(16, 185, 129, 0.25);
            --tr-text-primary: #f8fafc;
            --tr-text-muted: #94a3b8;
            --tr-font-title: 'Outfit', sans-serif;
            --tr-font-body: 'Inter', sans-serif;
        }

        body {
            background-color: #030712;
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

        /* Ocultar barra de desplazamiento manteniendo la funcionalidad */
        .mobile-app-content::-webkit-scrollbar {
            width: 4px;
        }
        .mobile-app-content::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: var(--tr-bg-card);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--tr-border);
            border-radius: 20px;
            color: var(--tr-text-primary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
        }

        /* Botón de acción principal - Glow Neón */
        .btn-neon-green {
            background-color: var(--tr-green-primary);
            color: #030712;
            font-family: var(--tr-font-title);
            font-weight: 600;
            border: none;
            border-radius: 16px;
            padding: 14px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px var(--tr-green-glow);
        }

        .btn-neon-green:hover, .btn-neon-green:focus {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            color: #030712;
        }
        
        .btn-neon-green:active {
            transform: translateY(0);
        }

        /* Botones secundarios oscuros */
        .btn-dark-secondary {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--tr-border);
            color: var(--tr-text-primary);
            font-family: var(--tr-font-title);
            font-weight: 500;
            border-radius: 16px;
            padding: 12px 20px;
            transition: all 0.2s ease;
        }

        .btn-dark-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            color: var(--tr-text-primary);
        }

        /* Títulos estilizados */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--tr-font-title);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .text-muted-custom {
            color: var(--tr-text-muted);
        }

        /* Barra de navegación inferior móvil */
        .mobile-bottom-nav {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 72px;
            background: rgba(13, 17, 26, 0.9);
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

        /* Formularios y Inputs */
        .form-control-custom {
            background-color: rgba(255, 255, 255, 0.05) !important;
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
            color: #64748b !important;
        }

        /* Switch Custom */
        .form-check-input-custom {
            width: 3.2em !important;
            height: 1.8em !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
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

        /* Tramos / Insignias de Ruta */
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
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
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

        /* Leaflet Map Styling */
        #leaflet-map {
            width: 100%;
            height: 100%;
            border-radius: 0;
            z-index: 1;
        }

        .leaflet-bar {
            border: 1px solid var(--tr-border) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
            border-radius: 12px !important;
            overflow: hidden;
        }

        .leaflet-bar a {
            background-color: #151c2b !important;
            color: var(--tr-text-primary) !important;
            border-bottom: 1px solid var(--tr-border) !important;
            transition: all 0.2s ease;
        }

        .leaflet-bar a:hover {
            background-color: #1e293b !important;
        }

        .leaflet-control-attribution {
            background-color: rgba(13, 17, 26, 0.7) !important;
            color: var(--tr-text-muted) !important;
            font-size: 9px !important;
        }

        /* Custom Map Pins */
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
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @keyframes pulse-pin-blue {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        /* Custom font-size utilities (Bootstrap 5 only goes to fs-6) */
        .fs-7 { font-size: 0.85rem !important; }
        .fs-8 { font-size: 0.75rem !important; }
        .fs-9 { font-size: 0.65rem !important; }

        /* Custom font-weight for extra bold (Bootstrap 5 lacks fw-extrabold) */
        .fw-extrabold { font-weight: 800 !important; }

        /* Pulse animation for live indicators */
        .animate-pulse {
            animation: animate-pulse-kf 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes animate-pulse-kf {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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

        /* Hover white text utility */
        .hover-white {
            transition: color 0.2s ease;
        }
        .hover-white:hover {
            color: var(--tr-text-primary) !important;
        }

        /* Blur utility */
        .blur-md {
            filter: blur(12px);
        }

        /* Smooth scrollbar for route lists */
        .d-flex.flex-column.gap-2::-webkit-scrollbar {
            width: 3px;
        }
        .d-flex.flex-column.gap-2::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
    </style>
</head>
<body>

    <div class="mobile-frame-container">
        <div class="mobile-app-content" id="app-viewport">
            @yield('content')
        </div>
        @yield('bottom-nav')
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Leaflet.js JS (Mapeo Interactivo) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Axios Client HTTP para asincronía -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @stack('scripts')
</body>
</html>
