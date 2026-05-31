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
        Schema::create('historial_rutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('punto_1_id')
                  ->constrained('puntos_navegacion')
                  ->onDelete('cascade');
            $table->foreignId('punto_2_id')
                  ->constrained('puntos_navegacion')
                  ->onDelete('cascade');
            $table->bigInteger('tiempo');
            $table->foreignId('horario_id')
                  ->constrained('horarios')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_rutas');
    }
};
