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
        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('conductor_id')->nullable()->after('id')->constrained('conductores')->nullOnDelete();
            $table->foreignId('ruta_id')->nullable()->after('conductor_id')->constrained('rutas')->nullOnDelete();
            $table->integer('num_combi')->nullable()->after('ruta_id');
            $table->string('color')->nullable()->after('num_combi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['conductor_id']);
            $table->dropForeign(['ruta_id']);
            $table->dropColumn(['conductor_id', 'ruta_id', 'num_combi', 'color']);
        });
    }
};
