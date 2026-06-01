<?php

namespace Database\Seeders;

use App\Models\Ruta;
use App\Models\PuntoNavegacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RutaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ruta de las carpetas de datos
        $geojsonPath = base_path('rutas-transporte-publico-zitacuaro');

        if (!File::isDirectory($geojsonPath)) {
            $this->command->error("El directorio {$geojsonPath} no existe.");
            return;
        }

        // Obtener todos los archivos .geojson en la carpeta
        $files = File::files($geojsonPath);
        $this->command->info("Se encontraron " . count($files) . " archivos GeoJSON en Zitácuaro.");

        $routesCount = 0;
        $pointsCount = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'geojson') {
                continue;
            }

            try {
                // Leer y decodificar el archivo GeoJSON
                $jsonContent = File::get($file->getRealPath());
                $data = json_decode($jsonContent, true);

                if (!$data || !isset($data['features']) || empty($data['features'])) {
                    continue;
                }

                // Extraemos las propiedades y geometría de la primera feature
                $feature = $data['features'][0];
                $properties = $feature['properties'] ?? [];
                $geometry = $feature['geometry'] ?? [];

                // Si no es un LineString, pasamos
                if (($geometry['type'] ?? '') !== 'LineString' || empty($geometry['coordinates'])) {
                    continue;
                }

                // Extraer el color y nombre de la ruta
                $colorHex = $properties['colour'] ?? '#10b981';
                $routeName = $properties['name'] ?? 'Ruta Sin Nombre (' . $file->getFilenameWithoutExtension() . ')';
                
                // Dado que el campo 'color' en la base de datos es string y único,
                // combinamos el color con el nombre e ID de archivo para asegurar unicidad absoluta.
                $colorDbString = substr("{$colorHex} - {$routeName} ({$file->getFilenameWithoutExtension()})", 0, 190);

                // Crear la ruta utilizando el ORM Eloquent
                $ruta = Ruta::create([
                    'color' => $colorDbString,
                ]);

                $routesCount++;

                // Iteramos sobre las coordenadas del LineString y las guardamos en puntos_navegacion
                $coordinates = $geometry['coordinates'];
                foreach ($coordinates as $index => $coord) {
                    $lng = $coord[0];
                    $lat = $coord[1];

                    // Tipo de giro aleatorio o por defecto para cumplir con la restricción enum de la migración:
                    // ['u_turn', 'right', 'straight', 'left']
                    $tipoDeGiro = 'straight';
                    if ($index > 0 && $index < count($coordinates) - 1) {
                        // Pequeña lógica para simular giros aleatorios en puntos intermedios
                        $rand = rand(1, 100);
                        if ($rand > 95) {
                            $tipoDeGiro = 'right';
                        } elseif ($rand > 90) {
                            $tipoDeGiro = 'left';
                        }
                    }

                    // Insertar usando el ORM Eloquent
                    PuntoNavegacion::create([
                        'ruta_id'      => $ruta->id,
                        'latitud'      => $lat,
                        'longitud'     => $lng,
                        'tipo_de_giro' => $tipoDeGiro,
                        'instruccion'  => "Avanzar por trayecto (" . ($index + 1) . ")",
                    ]);

                    $pointsCount++;
                }

            } catch (\Exception $e) {
                $this->command->error("Error al procesar el archivo {$file->getFilename()}: " . $e->getMessage());
            }
        }

        $this->command->info("PROCESO TERMINADO CON ÉXITO:");
        $this->command->info("- Rutas insertadas en la BD: {$routesCount}");
        $this->command->info("- Puntos de navegación (coordenadas geográficas): {$pointsCount}");
    }
}
