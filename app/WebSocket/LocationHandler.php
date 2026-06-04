<?php

namespace App\WebSocket;

use App\Models\Conductor;
use App\Models\Location;
use Workerman\Connection\TcpConnection;

/**
 * Handler de WebSocket para ubicaciones en tiempo real con autenticación
 * de conductores y suscripción de usuarios por color de ruta.
 */
class LocationHandler
{
    /**
     * Se ejecuta cuando un nuevo cliente se conecta al WebSocket.
     */
    public static function onConnect(TcpConnection $connection): void
    {
        $connection->isDriver = false;
        $connection->conductorId = null;
        $connection->conductorRutaId = null;
        $connection->conductorColor = null;
        $connection->conductorNumCombi = null;
        $connection->subscribedColors = [];

        $clientId = $connection->id;
        echo "\n┌─────────────────────────────────────────┐\n";
        echo "│  ✅ Nueva conexión: Cliente #{$clientId}         │\n";
        echo "│  IP: " . str_pad($connection->getRemoteIp(), 34) . "│\n";
        echo "└─────────────────────────────────────────┘\n";
    }

    /**
     * Se ejecuta cuando se recibe un mensaje del cliente.
     */
    public static function onMessage(TcpConnection $connection, $data): void
    {
        $clientId = $connection->id;
        $timestamp = date('Y-m-d H:i:s');

        $message = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($message['type'])) {
            $connection->send(json_encode([
                'type' => 'error',
                'message' => 'JSON inválido o falta el campo "type".',
            ]));
            echo "[{$timestamp}] ❌ Cliente #{$clientId} - Mensaje inválido: {$data}\n";
            return;
        }

        switch ($message['type']) {
            case 'auth':
                self::handleAuth($connection, $message, $timestamp);
                break;

            case 'location':
                self::handleLocation($connection, $message, $timestamp);
                break;

            case 'subscribe':
                self::handleSubscribe($connection, $message, $timestamp);
                break;

            case 'unsubscribe':
                self::handleUnsubscribe($connection, $message, $timestamp);
                break;

            default:
                $connection->send(json_encode([
                    'type' => 'error',
                    'message' => "Tipo de mensaje desconocido: {$message['type']}",
                ]));
                echo "[{$timestamp}] ❌ Cliente #{$clientId} - Tipo desconocido: {$message['type']}\n";
                break;
        }
    }

    /**
     * Se ejecuta cuando un cliente se desconecta.
     */
    public static function onClose(TcpConnection $connection): void
    {
        $clientId = $connection->id;
        $role = !empty($connection->isDriver) ? "Conductor (Combi #{$connection->conductorNumCombi})" : "Usuario";

        echo "[" . date('Y-m-d H:i:s') . "] 🔌 {$role} - Cliente #{$clientId} desconectado.\n";

        if (!empty($connection->isDriver) && !empty($connection->conductorColor)) {
            self::notifyDriverDisconnected($connection);
        }
    }

    /**
     * Se ejecuta cuando ocurre un error en la conexión.
     */
    public static function onError(TcpConnection $connection, $code, $msg): void
    {
        $clientId = $connection->id;
        echo "[" . date('Y-m-d H:i:s') . "] ⚠️  Error en Cliente #{$clientId}: [{$code}] {$msg}\n";
    }

    // ──────────────────────────────────────────────
    //  HANDLERS DE MENSAJES
    // ──────────────────────────────────────────────

    /**
     * Maneja la autenticación de un conductor.
     *
     * Espera: { "type": "auth", "token": "..." }
     */
    private static function handleAuth(TcpConnection $connection, array $message, string $timestamp): void
    {
        $clientId = $connection->id;

        if (empty($message['token'])) {
            $connection->send(json_encode([
                'type' => 'auth_error',
                'message' => 'Token requerido.',
            ]));
            echo "[{$timestamp}] ❌ Cliente #{$clientId} - Auth sin token\n";
            return;
        }

        $conductor = self::resolveApp($connection, function () use ($message) {
            return Conductor::findByToken($message['token']);
        });

        if (!$conductor) {
            $connection->send(json_encode([
                'type' => 'auth_error',
                'message' => 'Token inválido o expirado.',
            ]));
            echo "[{$timestamp}] ❌ Cliente #{$clientId} - Token inválido\n";
            return;
        }

        $ruta = $conductor->ruta;

        // Marcar conexión como conductor autenticado
        $connection->isDriver = true;
        $connection->conductorId = $conductor->id;
        $connection->conductorRutaId = $conductor->ruta_id;
        $connection->conductorColor = $ruta->color;
        $connection->conductorNumCombi = $conductor->num_combi;

        $connection->send(json_encode([
            'type' => 'auth_ok',
            'message' => 'Autenticado correctamente.',
            'conductor_id' => $conductor->id,
            'color' => $ruta->color,
            'num_combi' => $conductor->num_combi,
            'ruta_id' => $ruta->id,
        ]));

        echo "[{$timestamp}] 🔑 Conductor autenticado - Cliente #{$clientId}\n";
        echo "   ├─ Conductor ID: {$conductor->id} ({$conductor->nombre} {$conductor->apellido})\n";
        echo "   ├─ Color:        {$ruta->color}\n";
        echo "   └─ Combi #:      {$conductor->num_combi}\n";
    }

    /**
     * Maneja una ubicación enviada por un conductor autenticado.
     *
     * Espera: { "type": "location", "latitud": 19.43, "longitud": -99.13, "orientacion": 180.5, "velocidad": 45.3 }
     */
    private static function handleLocation(TcpConnection $connection, array $message, string $timestamp): void
    {
        $clientId = $connection->id;
        echo "location";
        // 🔒 VERIFICACIÓN DE AUTENTICACIÓN
        if (empty($connection->isDriver)) {
            $connection->send(json_encode([
                'type' => 'error',
                'message' => 'No autenticado. Debes enviar un mensaje de autenticación primero.',
            ]));
            echo "[{$timestamp}] 🚫 Cliente #{$clientId} - Intento de enviar location sin autenticación\n";
            return;
        }

        $campos = ['latitud', 'longitud', 'orientacion', 'velocidad'];
        $faltantes = [];

        foreach ($campos as $campo) {
            if (!isset($message[$campo])) {
                $faltantes[] = $campo;
            }
        }

        if (!empty($faltantes)) {
            $connection->send(json_encode([
                'type' => 'error',
                'message' => 'Campos faltantes: ' . implode(', ', $faltantes),
            ]));
            return;
        }

        // Validar rangos
        $errors = [];
        if ($message['latitud'] < -90 || $message['latitud'] > 90) {
            $errors[] = 'latitud debe estar entre -90 y 90';
        }
        if ($message['longitud'] < -180 || $message['longitud'] > 180) {
            $errors[] = 'longitud debe estar entre -180 y 180';
        }
        if ($message['orientacion'] < 0 || $message['orientacion'] > 360) {
            $errors[] = 'orientacion debe estar entre 0 y 360';
        }
        if ($message['velocidad'] < 0) {
            $errors[] = 'velocidad no puede ser negativa';
        }

        if (!empty($errors)) {
            $connection->send(json_encode([
                'type' => 'error',
                'message' => 'Errores de validación',
                'errors' => $errors,
            ]));
            return;
        }

        $latitud = (float) $message['latitud'];
        $longitud = (float) $message['longitud'];
        $orientacion = (float) $message['orientacion'];
        $velocidad = (float) $message['velocidad'];
        $color = $connection->conductorColor;
        $numCombi = $connection->conductorNumCombi;

        // Imprimir en consola
        echo "\n[{$timestamp}] 📍 Location - Conductor #{$connection->conductorId} (Combi #{$numCombi})\n";
        echo "   ├─ Color:        {$color}\n";
        echo "   ├─ Latitud:      {$latitud}\n";
        echo "   ├─ Longitud:     {$longitud}\n";
        echo "   ├─ Orientación:  {$orientacion}°\n";
        echo "   └─ Velocidad:    {$velocidad} km/h\n";

        // Persistir en base de datos
        self::resolveApp($connection, function () use ($connection, $latitud, $longitud, $orientacion, $velocidad, $color, $numCombi) {
            Location::create([
                'conductor_id' => $connection->conductorId,
                'ruta_id' => $connection->conductorRutaId,
                'num_combi' => $numCombi,
                'color' => $color,
                'latitud' => $latitud,
                'longitud' => $longitud,
                'orientacion' => $orientacion,
                'velocidad' => $velocidad,
            ]);
        });

        // Transmitir SOLO a usuarios suscritos a este color
        $broadcastData = json_encode([
            'type' => 'location_update',
            'conductor_id' => $connection->conductorId,
            'color' => $color,
            'num_combi' => $numCombi,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'orientacion' => $orientacion,
            'velocidad' => $velocidad,
            'timestamp' => $timestamp,
            'timestamp_ms' => round(microtime(true) * 1000),
        ]);

        $receptores = 0;

        if (isset($connection->worker->connections)) {
            foreach ($connection->worker->connections as $con) {
                // Solo enviar a usuarios suscritos a este color (no al propio conductor)
                if ($con->id !== $clientId
                    && empty($con->isDriver)
                    && !empty($con->subscribedColors)
                    && in_array($color, $con->subscribedColors, true)
                ) {
                    $con->send($broadcastData);
                    $receptores++;
                }
            }
        }

        echo "   └─ Broadcast a {$receptores} usuario(s) suscrito(s) a este color\n";

        // Confirmar al conductor
        $connection->send(json_encode([
            'type' => 'location_ack',
            'success' => true,
            'message' => 'Ubicación recibida y transmitida.',
            'receptores' => $receptores,
            'timestamp' => $timestamp,
        ]));
    }

    /**
     * Maneja la suscripción de un usuario a uno o más colores de ruta.
     *
     * Espera: { "type": "subscribe", "colors": ["#FFFF00 - Amarilla...", "#FF0000 - Roja..."] }
     */
    private static function handleSubscribe(TcpConnection $connection, array $message, string $timestamp): void
    {
        $clientId = $connection->id;

        if (empty($message['colors']) || !is_array($message['colors'])) {
            $connection->send(json_encode([
                'type' => 'error',
                'message' => 'El campo "colors" debe ser un arreglo de colores.',
            ]));
            return;
        }

        $currentColors = $connection->subscribedColors ?? [];
        $newColors = array_unique(array_merge($currentColors, $message['colors']));
        $connection->subscribedColors = $newColors;

        $connection->send(json_encode([
            'type' => 'subscribed',
            'message' => 'Suscripción actualizada.',
            'colors' => $newColors,
        ]));

        echo "[{$timestamp}] 📬 Usuario #{$clientId} suscrito a " . count($message['colors']) . " color(es)\n";
        echo "   └─ Colores: " . implode(', ', $message['colors']) . "\n";
    }

    /**
     * Maneja la desuscripción de un usuario de uno o más colores.
     *
     * Espera: { "type": "unsubscribe", "colors": ["#FFFF00 - Amarilla..."] }
     */
    private static function handleUnsubscribe(TcpConnection $connection, array $message, string $timestamp): void
    {
        $clientId = $connection->id;

        if (empty($message['colors']) || !is_array($message['colors'])) {
            $connection->send(json_encode([
                'type' => 'error',
                'message' => 'El campo "colors" debe ser un arreglo de colores.',
            ]));
            return;
        }

        $currentColors = $connection->subscribedColors ?? [];
        $updatedColors = array_values(array_diff($currentColors, $message['colors']));
        $connection->subscribedColors = $updatedColors;

        $connection->send(json_encode([
            'type' => 'unsubscribed',
            'message' => 'Desuscripción exitosa.',
            'colors' => $updatedColors,
        ]));

        echo "[{$timestamp}] 📭 Usuario #{$clientId} desuscrito de " . count($message['colors']) . " color(es)\n";
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    /**
     * Ejecuta un callback con la aplicación de Laravel disponible,
     * manejando reconexión de base de datos si es necesario.
     */
    private static function resolveApp(TcpConnection $connection, callable $callback): mixed
    {
        $app = $connection->worker->laravelApp ?? null;

        if (!$app) {
            return null;
        }

        try {
            // Reconnect DB if connection was lost (Workerman long-running process)
            $db = $app->make('db');
            try {
                $db->getPdo();
            } catch (\Exception $e) {
                $db->reconnect();
            }
        } catch (\Exception $e) {
            // DB not available, continue anyway (broadcast still works)
        }

        return $callback();
    }

    /**
     * Notifica a los usuarios suscritos que un conductor se ha desconectado.
     */
    private static function notifyDriverDisconnected(TcpConnection $connection): void
    {
        $notification = json_encode([
            'type' => 'driver_disconnected',
            'conductor_id' => $connection->conductorId,
            'color' => $connection->conductorColor,
            'num_combi' => $connection->conductorNumCombi,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        $color = $connection->conductorColor;

        if (isset($connection->worker->connections)) {
            foreach ($connection->worker->connections as $con) {
                if (empty($con->isDriver)
                    && !empty($con->subscribedColors)
                    && in_array($color, $con->subscribedColors, true)
                ) {
                    $con->send($notification);
                }
            }
        }
    }
}
