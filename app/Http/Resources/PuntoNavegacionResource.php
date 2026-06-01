<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource para serializar un punto de navegación.
 */
class PuntoNavegacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'latitud' => (float) $this->latitud,
            'longitud' => (float) $this->longitud,
            'tipo_de_giro' => $this->tipo_de_giro,
            'instruccion' => $this->instruccion,
        ];
    }
}
