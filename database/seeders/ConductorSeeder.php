<?php

namespace Database\Seeders;

use App\Models\Conductor;
use App\Models\Ruta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ConductorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crea un conductor por cada ruta con:
     * - num_combi: 1
     * - password: 1234 (hasheado con bcrypt)
     * - nombre, apellido e id_conductor: aleatorios
     */
    public function run(): void
    {
        $rutas = Ruta::all();

        if ($rutas->isEmpty()) {
            $this->command->warn('No hay rutas en la base de datos. Ejecuta RutaSeeder primero.');
            return;
        }

        $nombres = [
            'Juan', 'Pedro', 'José', 'Miguel', 'Carlos', 'Luis', 'Jorge', 'Francisco',
            'Antonio', 'Javier', 'Manuel', 'Rafael', 'Alejandro', 'Fernando', 'Ricardo',
            'Alberto', 'Roberto', 'Eduardo', 'Héctor', 'Sergio', 'Marco', 'Oscar',
            'Andrés', 'Mario', 'Arturo', 'Hugo', 'Ramón', 'Gerardo', 'Enrique', 'Diego',
        ];

        $apellidos = [
            'García', 'Martínez', 'López', 'Hernández', 'González', 'Rodríguez',
            'Pérez', 'Sánchez', 'Ramírez', 'Cruz', 'Flores', 'Morales', 'Vázquez',
            'Jiménez', 'Ruiz', 'Torres', 'Reyes', 'Ortiz', 'Mendoza', 'Aguilar',
            'Castillo', 'Romero', 'Chávez', 'Medina', 'Moreno', 'Álvarez', 'Vega',
        ];

        $faker = fake();
        $count = 0;

        foreach ($rutas as $ruta) {
            $nombre = $faker->randomElement($nombres);
            $apellido = $faker->randomElement($apellidos);
            $idConductor = 'COND-' . strtoupper($faker->bothify('??###'));

            Conductor::firstOrCreate(
                [
                    'ruta_id' => $ruta->id,
                    'num_combi' => 1,
                ],
                [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'id_conductor' => $idConductor,
                    'password' => Hash::make('1234'),
                ]
            );

            $count++;
        }

        $this->command->info("{$count} conductores creados (1 por ruta, num_combi=1, password=1234).");
    }
}
