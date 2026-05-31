<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que registra el historial de tiempos entre dos puntos de navegación.
 *
 * Almacena el tiempo en segundos que toma recorrer entre punto_1 y punto_2,
 * asociado a un horario específico para análisis temporal.
 *
 * @property int $id
 * @property int $punto_1_id
 * @property int $punto_2_id
 * @property int $tiempo
 * @property int $horario_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\PuntoNavegacion $punto1
 * @property-read \App\Models\PuntoNavegacion $punto2
 * @property-read \App\Models\Horario $horario
 */
class HistorialRuta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'historial_rutas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'punto_1_id',
        'punto_2_id',
        'tiempo',
        'horario_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tiempo' => 'integer',
        ];
    }

    /**
     * Obtiene el punto de navegación de origen.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\PuntoNavegacion, $this>
     */
    public function punto1(): BelongsTo
    {
        return $this->belongsTo(PuntoNavegacion::class, 'punto_1_id');
    }

    /**
     * Obtiene el punto de navegación de destino.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\PuntoNavegacion, $this>
     */
    public function punto2(): BelongsTo
    {
        return $this->belongsTo(PuntoNavegacion::class, 'punto_2_id');
    }

    /**
     * Obtiene el horario asociado a este registro de historial.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Horario, $this>
     */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }
}
