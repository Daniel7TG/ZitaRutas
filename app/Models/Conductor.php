<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Modelo que representa un conductor de transporte público.
 *
 * Implementa autenticación para que los conductores puedan iniciar sesión
 * usando: color de ruta + número de combi + contraseña.
 *
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property int $num_combi
 * @property string $id_conductor
 * @property int $ruta_id
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Ruta $ruta
 */
class Conductor extends Authenticatable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'conductores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'num_combi',
        'id_conductor',
        'ruta_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'num_combi' => 'integer',
        ];
    }

    /**
     * Obtiene la ruta asignada a este conductor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Ruta, $this>
     */
    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}
