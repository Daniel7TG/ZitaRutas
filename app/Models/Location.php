<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa la ubicación en tiempo real de un dispositivo.
 *
 * Los datos se reciben via WebSocket desde dispositivos remotos
 * y contienen coordenadas GPS, orientación y velocidad.
 *
 * @property int $id
 * @property int|null $conductor_id
 * @property int|null $ruta_id
 * @property int|null $num_combi
 * @property string|null $color
 * @property float $latitud
 * @property float $longitud
 * @property float $orientacion  Grados (0-360)
 * @property float $velocidad    km/h
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Conductor|null $conductor
 * @property-read \App\Models\Ruta|null $ruta
 */
class Location extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conductor_id',
        'ruta_id',
        'num_combi',
        'color',
        'latitud',
        'longitud',
        'orientacion',
        'velocidad',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'orientacion' => 'decimal:2',
            'velocidad' => 'decimal:2',
            'num_combi' => 'integer',
        ];
    }

    /**
     * Obtiene el conductor asociado a esta ubicación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Conductor, $this>
     */
    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }

    /**
     * Obtiene la ruta asociada a esta ubicación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Ruta, $this>
     */
    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}
