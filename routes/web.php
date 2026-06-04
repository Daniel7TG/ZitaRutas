<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConductorController;
use App\Http\Controllers\HomeController;
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

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::resource('rutas', RutaController::class);
Route::resource('puntos-navegacion', PuntoNavegacionController::class);
Route::resource('horarios', HorarioController::class);
Route::resource('historial-rutas', HistorialRutaController::class);
Route::resource('conductores', ConductorController::class);
