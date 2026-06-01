<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource para serializar una ruta con sus relaciones.
 *
 * Incluye el color como identificador primario,
 * junto con las colecciones anidadas de horarios y puntos de navegación.
 */
class RutaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'color' => $this->color,
            'horarios' => HorarioResource::collection($this->whenLoaded('horarios')),
            'puntos_navegacion' => PuntoNavegacionResource::collection($this->whenLoaded('puntosNavegacion')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
