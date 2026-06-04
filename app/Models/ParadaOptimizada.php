<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParadaOptimizada extends Model
{
    protected $table = 'paradas_optimizadas';

    protected $fillable = [
        'ruta_id',
        'latitud',
        'longitud',
        'orden',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'orden' => 'integer',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class);
    }
}
