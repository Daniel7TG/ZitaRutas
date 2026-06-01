<?php

use App\Http\Controllers\Api\ConductorAuthController;
use App\Http\Controllers\Api\RutaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la API REST de ZitaRutas.
| Todas las rutas tienen el prefijo /api y responden en JSON.
|
*/

Route::post('/conductor/login', [ConductorAuthController::class, 'login']);
Route::post('/conductor/logout', [ConductorAuthController::class, 'logout']);

Route::prefix('rutas')->group(function () {
    // GET    /api/rutas          → Listar todas las rutas
    Route::get('/', [RutaApiController::class, 'index'])->name('api.rutas.index');

    // POST   /api/rutas          → Crear una nueva ruta
    Route::post('/', [RutaApiController::class, 'store'])->name('api.rutas.store');

    // GET    /api/rutas/{color}  → Obtener una ruta por color
    Route::get('/{color}', [RutaApiController::class, 'show'])->name('api.rutas.show');

    // PUT    /api/rutas/{color}  → Actualizar completa una ruta por color
    Route::put('/{color}', [RutaApiController::class, 'update'])->name('api.rutas.update');

    // PATCH  /api/rutas/{color}  → Actualizar parcialmente una ruta por color
    Route::patch('/{color}', [RutaApiController::class, 'update'])->name('api.rutas.patch');

    // DELETE /api/rutas/{color}  → Eliminar una ruta por color
    Route::delete('/{color}', [RutaApiController::class, 'destroy'])->name('api.rutas.destroy');
});
