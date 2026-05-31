<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo que representa una ruta de transporte público.
 *
 * Cada ruta se identifica por un color único y tiene asociados
 * puntos de navegación, horarios y conductores.
 *
 * @property int $id
 * @property string $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PuntoNavegacion> $puntosNavegacion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Horario> $horarios
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conductor> $conductores
 */
class Ruta extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rutas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'color',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * Obtiene los puntos de navegación asociados a esta ruta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\PuntoNavegacion, $this>
     */
    public function puntosNavegacion(): HasMany
    {
        return $this->hasMany(PuntoNavegacion::class, 'ruta_id');
    }

    /**
     * Obtiene los horarios asociados a esta ruta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Horario, $this>
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'ruta_id');
    }

    /**
     * Obtiene los conductores asignados a esta ruta.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Conductor, $this>
     */
    public function conductores(): HasMany
    {
        return $this->hasMany(Conductor::class, 'ruta_id');
    }
}
