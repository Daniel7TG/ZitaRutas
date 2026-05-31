<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo que representa un punto de navegación dentro de una ruta.
 *
 * Cada punto contiene coordenadas geográficas, un tipo de giro
 * y una instrucción opcional para guiar al conductor.
 *
 * @property int $id
 * @property int $ruta_id
 * @property float $latitud
 * @property float $longitud
 * @property string $tipo_de_giro
 * @property string|null $instruccion
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Ruta $ruta
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HistorialRuta> $historialComoPunto1
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HistorialRuta> $historialComoPunto2
 */
class PuntoNavegacion extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'puntos_navegacion';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ruta_id',
        'latitud',
        'longitud',
        'tipo_de_giro',
        'instruccion',
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
        ];
    }

    /**
     * Obtiene la ruta a la que pertenece este punto de navegación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Ruta, $this>
     */
    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }

    /**
     * Obtiene los registros de historial donde este punto es el punto de origen.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\HistorialRuta, $this>
     */
    public function historialComoPunto1(): HasMany
    {
        return $this->hasMany(HistorialRuta::class, 'punto_1_id');
    }

    /**
     * Obtiene los registros de historial donde este punto es el punto de destino.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\HistorialRuta, $this>
     */
    public function historialComoPunto2(): HasMany
    {
        return $this->hasMany(HistorialRuta::class, 'punto_2_id');
    }
}
