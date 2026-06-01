<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRutaRequest;
use App\Http\Requests\UpdateRutaRequest;
use App\Http\Resources\RutaResource;
use App\Models\Ruta;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Controlador API para el CRUD de rutas.
 *
 * Todas las rutas se identifican por su color (primary key funcional).
 * Soporta creación con horarios y puntos de navegación en una sola petición,
 * así como actualización parcial o completa.
 */
class RutaApiController extends Controller
{
    /**
     * Lista todas las rutas con sus horarios y puntos de navegación.
     *
     * GET /api/rutas
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(): JsonResponse
    {
        $rutas = Ruta::with(['horarios', 'puntosNavegacion'])->get();

        return response()->json([
            'success' => true,
            'data' => RutaResource::collection($rutas),
        ]);
    }

    /**
     * Muestra una ruta específica identificada por su color.
     *
     * GET /api/rutas/{color}
     *
     * @param string $color El color único que identifica la ruta
     */
    public function show(string $color): JsonResponse
    {
        $ruta = Ruta::where('color', $color)
            ->with(['horarios', 'puntosNavegacion'])
            ->first();

        if (!$ruta) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró una ruta con el color '{$color}'.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RutaResource($ruta),
        ]);
    }

    /**
     * Crea una nueva ruta con sus horarios y puntos de navegación.
     *
     * POST /api/rutas
     *
     * Body JSON:
     * {
     *   "color": "#FF5733",
     *   "horarios": [
     *     { "name": "Mañana", "identifier": "AM" },
     *     { "name": "Tarde", "identifier": "PM" }
     *   ],
     *   "puntos_navegacion": [
     *     {
     *       "latitud": 19.4326077,
     *       "longitud": -99.1332080,
     *       "tipo_de_giro": "straight",
     *       "instruccion": "Continuar recto por Av. Reforma"
     *     }
     *   ]
     * }
     */
    public function store(StoreRutaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $ruta = DB::transaction(function () use ($validated) {
            // Crear la ruta
            $ruta = Ruta::create([
                'color' => $validated['color'],
            ]);

            // Crear horarios si vienen en la petición
            if (!empty($validated['horarios'])) {
                foreach ($validated['horarios'] as $horarioData) {
                    $ruta->horarios()->create($horarioData);
                }
            }

            // Crear puntos de navegación si vienen en la petición
            if (!empty($validated['puntos_navegacion'])) {
                foreach ($validated['puntos_navegacion'] as $puntoData) {
                    $ruta->puntosNavegacion()->create($puntoData);
                }
            }

            return $ruta;
        });

        $ruta->load(['horarios', 'puntosNavegacion']);

        return response()->json([
            'success' => true,
            'message' => 'Ruta creada exitosamente.',
            'data' => new RutaResource($ruta),
        ], 201);
    }

    /**
     * Actualiza una ruta existente, parcial o completamente.
     *
     * PUT|PATCH /api/rutas/{color}
     *
     * Actualización parcial — solo envía los campos que deseas modificar:
     * - Solo color:              { "color": "#00FF00" }
     * - Solo horarios:           { "horarios": [...] }
     * - Solo puntos_navegacion:  { "puntos_navegacion": [...] }
     * - Combinación:             { "color": "#00FF00", "horarios": [...] }
     *
     * Cuando se envían horarios o puntos_navegacion, se reemplazan
     * todos los registros existentes (sync-replace).
     *
     * @param string $color El color actual de la ruta
     */
    public function update(UpdateRutaRequest $request, string $color): JsonResponse
    {
        $ruta = Ruta::where('color', $color)->first();

        if (!$ruta) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró una ruta con el color '{$color}'.",
            ], 404);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($ruta, $validated) {
            // Actualizar el color si viene en la petición
            if (isset($validated['color'])) {
                $ruta->update(['color' => $validated['color']]);
            }

            // Reemplazar horarios si vienen en la petición (sync-replace)
            if (array_key_exists('horarios', $validated)) {
                $ruta->horarios()->delete();
                foreach ($validated['horarios'] as $horarioData) {
                    $ruta->horarios()->create($horarioData);
                }
            }

            // Reemplazar puntos de navegación si vienen en la petición (sync-replace)
            if (array_key_exists('puntos_navegacion', $validated)) {
                $ruta->puntosNavegacion()->delete();
                foreach ($validated['puntos_navegacion'] as $puntoData) {
                    $ruta->puntosNavegacion()->create($puntoData);
                }
            }
        });

        $ruta->refresh();
        $ruta->load(['horarios', 'puntosNavegacion']);

        return response()->json([
            'success' => true,
            'message' => 'Ruta actualizada exitosamente.',
            'data' => new RutaResource($ruta),
        ]);
    }

    /**
     * Elimina una ruta y sus relaciones (cascade).
     *
     * DELETE /api/rutas/{color}
     *
     * @param string $color El color de la ruta a eliminar
     */
    public function destroy(string $color): JsonResponse
    {
        $ruta = Ruta::where('color', $color)->first();

        if (!$ruta) {
            return response()->json([
                'success' => false,
                'message' => "No se encontró una ruta con el color '{$color}'.",
            ], 404);
        }

        $ruta->delete();

        return response()->json([
            'success' => true,
            'message' => "Ruta con color '{$color}' eliminada exitosamente.",
        ]);
    }
}
