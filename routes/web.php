<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\HistorialRutaController;
use App\Http\Controllers\PuntoNavegacionController;
use App\Http\Controllers\RutaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas web de la aplicación ZitaRutas.
| Estas rutas son cargadas por el RouteServiceProvider y todas
| están asignadas al grupo de middleware "web".
|
*/

// Página de bienvenida
use App\Models\Ruta;

// Página de bienvenida dinámica con carga de rutas reales desde la BD usando Eloquent ORM
Route::get('/', function () {
    $screen = request()->query('screen', 'welcome');
    $routeId = request()->query('route_id');

    // Carga selectiva: solo eager-load puntos de navegación en pantallas que usan el mapa
    if (in_array($screen, ['routes', 'tracking', 'route-detail'])) {
        // Solo cargar coordenadas para las rutas del mapa (mejora de rendimiento)
        $rutas = Ruta::with(['puntosNavegacion' => function ($query) {
            $query->orderBy('id');
        }])->get();
    } else {
        // Welcome, permissions, favorites: no necesitan coordenadas GPS
        $rutas = Ruta::all();
    }

    return view('welcome', compact('rutas'));
})->name('home');

// Rutas de autenticación para conductores
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas de recursos
Route::resource('rutas', RutaController::class);
Route::resource('puntos-navegacion', PuntoNavegacionController::class);
Route::resource('horarios', HorarioController::class);
Route::resource('historial-rutas', HistorialRutaController::class);
Route::resource('conductores', ConductorController::class);
