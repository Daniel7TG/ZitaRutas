<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

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
        'api_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
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

    /**
     * Genera un nuevo token de API para este conductor.
     *
     * El token se almacena hasheado (SHA-256) en la base de datos.
     * Retorna el token en texto plano para que el conductor lo use.
     */
    public function createToken(): string
    {
        $plainTextToken = Str::random(64);

        $this->forceFill([
            'api_token' => hash('sha256', $plainTextToken),
        ])->save();

        return $plainTextToken;
    }

    /**
     * Invalida el token actual del conductor.
     */
    public function revokeToken(): void
    {
        $this->forceFill([
            'api_token' => null,
        ])->save();
    }

    /**
     * Busca un conductor por su ruta (color) y número de combi.
     */
    public static function findByRouteAndCombi(string $color, int $numCombi): ?self
    {
        $ruta = Ruta::where('color', $color)->first();

        if (!$ruta) {
            return null;
        }

        return self::where('ruta_id', $ruta->id)
            ->where('num_combi', $numCombi)
            ->first();
    }

    /**
     * Busca un conductor por su token de API (validado contra el hash almacenado).
     */
    public static function findByToken(string $plainTextToken): ?self
    {
        $hashedToken = hash('sha256', $plainTextToken);

        return self::where('api_token', $hashedToken)->first();
    }
}
