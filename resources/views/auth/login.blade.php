@extends('layouts.app')

@section('content')
<div class="d-flex flex-column justify-content-between h-100 p-4" style="min-height: 800px;">
    <!-- Encabezado -->
    <div class="mt-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fa-solid fa-leaf text-success fs-4"></i>
            <span class="fs-5 fw-bold text-white" style="font-family: var(--tr-font-title);">ZitaRutas</span>
        </div>
        <hr class="border-secondary border-opacity-25 my-2">
    </div>

    <!-- Contenido del Login -->
    <div class="my-auto">
        <div class="text-center mb-4">
            <div class="position-relative mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <div class="position-relative bg-dark border border-secondary border-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    <i class="fa-solid fa-user-shield text-success fs-2"></i>
                </div>
            </div>
            <h2 class="text-white fs-4 mb-1" style="font-family: var(--tr-font-title);">Acceso de Conductor</h2>
            <p class="text-muted-custom fs-7">Ingresa tus credenciales para administrar rutas</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger rounded-3 fs-7 mb-3">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success bg-success bg-opacity-10 border border-success border-opacity-25 text-success rounded-3 fs-7 mb-3">
                <i class="fa-solid fa-circle-check me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label text-muted-custom fs-7 mb-1">
                    <i class="fa-solid fa-envelope me-1"></i> Correo electrónico
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="form-control form-control-custom"
                       placeholder="conductor@zitarutas.com" required autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label text-muted-custom fs-7 mb-1">
                    <i class="fa-solid fa-lock me-1"></i> Contraseña
                </label>
                <input type="password" id="password" name="password"
                       class="form-control form-control-custom"
                       placeholder="••••••••" required>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input form-check-input-custom" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label text-muted-custom fs-8" for="remember">
                        Recordarme
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-neon-green w-100 py-3 mb-3 d-flex align-items-center justify-content-center gap-2">
                <i class="fa-solid fa-right-to-bracket fs-5"></i> Iniciar Sesión
            </button>
        </form>

        <div class="text-center">
            <small class="text-muted-custom fs-8">
                <i class="fa-solid fa-shield-halved me-1"></i> Protegido con CSRF Token de Laravel
            </small>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center mb-2">
        <a href="/" class="text-muted-custom text-decoration-none fs-7 hover-white">
            <i class="fa-solid fa-chevron-left me-1"></i> Volver al mapa
        </a>
    </div>
</div>
@endsection
