<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo que representa un horario asociado a una ruta.
 *
 * Los horarios permiten clasificar los registros de historial
 * por franjas horarias o turnos (e.g. "Mañana", "Tarde").
 *
 * @property int $id
 * @property int $ruta_id
 * @property string $time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Ruta $ruta
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HistorialRuta> $historialRutas
 */
class Horario extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'horarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ruta_id',
        'time',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'time' => 'datetime:h:ia', // Automatically formats to 7:00am etc. when serialized or cast
        ];
    }

    /**
     * Obtiene la ruta a la que pertenece este horario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Ruta, $this>
     */
    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }

    /**
     * Obtiene los registros de historial asociados a este horario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\HistorialRuta, $this>
     */
    public function historialRutas(): HasMany
    {
        return $this->hasMany(HistorialRuta::class, 'horario_id');
    }
}
