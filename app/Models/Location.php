<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa la ubicación en tiempo real de un dispositivo.
 *
 * Los datos se reciben via WebSocket desde dispositivos remotos
 * y contienen coordenadas GPS, orientación y velocidad.
 *
 * @property int $id
 * @property float $latitud
 * @property float $longitud
 * @property float $orientacion  Grados (0-360)
 * @property float $velocidad    km/h
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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
        ];
    }
}
