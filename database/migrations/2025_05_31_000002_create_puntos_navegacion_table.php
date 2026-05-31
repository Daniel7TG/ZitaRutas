<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('puntos_navegacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')
                  ->constrained('rutas')
                  ->onDelete('cascade');
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->enum('tipo_de_giro', ['u_turn', 'right', 'straight', 'left']);
            $table->string('instruccion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntos_navegacion');
    }
};
