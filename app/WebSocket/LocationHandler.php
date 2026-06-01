<?php

namespace App\WebSocket;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

/**
 * Handler de WebSocket para recibir datos de ubicación en tiempo real.
 *
 * Recibe JSON desde dispositivos remotos con la estructura:
 * {
 *   "latitud": 19.4326077,
 *   "longitud": -99.1332080,
 *   "orientacion": 180.50,
 *   "velocidad": 45.30
 * }
 *
 * Por ahora solo imprime los datos recibidos en la consola del servidor.
 */
class LocationHandler
{
    /**
     * Se ejecuta cuando un nuevo cliente se conecta al WebSocket.
     */
    public static function onConnect(TcpConnection $connection): void
    {
        $clientId = $connection->id;
        echo "\n┌─────────────────────────────────────────┐\n";
        echo "│  ✅ Nueva conexión: Cliente #{$clientId}         │\n";
        echo "│  IP: " . str_pad($connection->getRemoteIp(), 34) . "│\n";
        echo "└─────────────────────────────────────────┘\n";
    }

    /**
     * Se ejecuta cuando se recibe un mensaje del cliente.
     *
     * Espera un JSON con: latitud, longitud, orientacion, velocidad.
     */
    public static function onMessage(TcpConnection $connection, $data): void
    {
        $clientId = $connection->id;
        $timestamp = date('Y-m-d H:i:s');

        // Decodificar JSON
        $location = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = json_encode([
                'success' => false,
                'message' => 'JSON inválido: ' . json_last_error_msg(),
            ]);
            $connection->send($errorMsg);
            echo "[{$timestamp}] ❌ Cliente #{$clientId} - JSON inválido: {$data}\n";
            return;
        }

        // Validar campos requeridos
        $camposRequeridos = ['latitud', 'longitud', 'orientacion', 'velocidad'];
        $camposFaltantes = [];

        foreach ($camposRequeridos as $campo) {
            if (!isset($location[$campo])) {
                $camposFaltantes[] = $campo;
            }
        }

        if (!empty($camposFaltantes)) {
            $errorMsg = json_encode([
                'success' => false,
                'message' => 'Campos faltantes: ' . implode(', ', $camposFaltantes),
            ]);
            $connection->send($errorMsg);
            echo "[{$timestamp}] ❌ Cliente #{$clientId} - Campos faltantes: " . implode(', ', $camposFaltantes) . "\n";
            return;
        }

        // Validar rangos
        $errors = [];
        if ($location['latitud'] < -90 || $location['latitud'] > 90) {
            $errors[] = 'latitud debe estar entre -90 y 90';
        }
        if ($location['longitud'] < -180 || $location['longitud'] > 180) {
            $errors[] = 'longitud debe estar entre -180 y 180';
        }
        if ($location['orientacion'] < 0 || $location['orientacion'] > 360) {
            $errors[] = 'orientacion debe estar entre 0 y 360';
        }
        if ($location['velocidad'] < 0) {
            $errors[] = 'velocidad no puede ser negativa';
        }

        if (!empty($errors)) {
            $errorMsg = json_encode([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errors,
            ]);
            $connection->send($errorMsg);
            echo "[{$timestamp}] ❌ Cliente #{$clientId} - Validación: " . implode(', ', $errors) . "\n";
            return;
        }

        // Imprimir en consola
        echo "\n[{$timestamp}] 📍 Location recibida - Cliente #{$clientId}\n";
        echo "   ├─ Latitud:      {$location['latitud']}\n";
        echo "   ├─ Longitud:     {$location['longitud']}\n";
        echo "   ├─ Orientación:  {$location['orientacion']}°\n";
        echo "   └─ Velocidad:    {$location['velocidad']} km/h\n";

        // Responder confirmación al cliente
        $response = json_encode([
            'success' => true,
            'message' => 'Location recibida correctamente.',
            'timestamp' => $timestamp,
        ]);
        $connection->send($response);
    }

    /**
     * Se ejecuta cuando un cliente se desconecta.
     */
    public static function onClose(TcpConnection $connection): void
    {
        $clientId = $connection->id;
        echo "[" . date('Y-m-d H:i:s') . "] 🔌 Cliente #{$clientId} desconectado.\n";
    }

    /**
     * Se ejecuta cuando ocurre un error en la conexión.
     */
    public static function onError(TcpConnection $connection, $code, $msg): void
    {
        $clientId = $connection->id;
        echo "[" . date('Y-m-d H:i:s') . "] ⚠️  Error en Cliente #{$clientId}: [{$code}] {$msg}\n";
    }
}
