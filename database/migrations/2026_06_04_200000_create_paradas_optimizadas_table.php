<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paradas_optimizadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained('rutas')->onDelete('cascade');
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->integer('orden');
            $table->timestamps();
            
            $table->index(['ruta_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paradas_optimizadas');
    }
};
