<?php

namespace App\Console\Commands;

use App\WebSocket\LocationHandler;
use Illuminate\Console\Command;
use Workerman\Worker;

/**
 * Comando Artisan para iniciar el servidor WebSocket de ubicaciones.
 *
 * Uso:
 *   php artisan websocket:serve              → Inicia en ws://0.0.0.0:8080
 *   php artisan websocket:serve --port=9090  → Inicia en puerto personalizado
 *   php artisan websocket:serve --host=127.0.0.1 → Solo conexiones locales
 */
class WebSocketServeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:serve
                            {--host=0.0.0.0 : Dirección IP del servidor}
                            {--port=8080 : Puerto del WebSocket}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicia el servidor WebSocket para recibir ubicaciones en tiempo real';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $host = $this->option('host');
        $port = $this->option('port');

        $this->info('');
        $this->info('╔═══════════════════════════════════════════╗');
        $this->info('║     🚀 ZitaRutas WebSocket Server        ║');
        $this->info('╠═══════════════════════════════════════════╣');
        $this->info("║  Escuchando en: ws://{$host}:{$port}      ");
        $this->info('║  Protocolo:     WebSocket                 ║');
        $this->info('║  Modelo:        Location                  ║');
        $this->info('╠═══════════════════════════════════════════╣');
        $this->info('║  Esperando conexiones de dispositivos...  ║');
        $this->info('╚═══════════════════════════════════════════╝');
        $this->info('');
        $this->info('Presiona Ctrl+C para detener el servidor.');
        $this->info('');

        // Crear el Worker de WebSocket
        $worker = new Worker("websocket://{$host}:{$port}");

        // Nombre del proceso (visible en el sistema operativo)
        $worker->name = 'ZitaRutas-LocationWS';

        // Un solo proceso (suficiente para desarrollo local)
        $worker->count = 1;

        // Registrar callbacks del handler
        $worker->onConnect = [LocationHandler::class, 'onConnect'];
        $worker->onMessage = [LocationHandler::class, 'onMessage'];
        $worker->onClose = [LocationHandler::class, 'onClose'];
        $worker->onError = [LocationHandler::class, 'onError'];

        // Adaptar $argv para que Workerman entienda el comando (espera 'start')
        global $argv;
        $argv = [$argv[0] ?? 'artisan', 'start'];

        // Iniciar el servidor (bloquea el proceso)
        Worker::runAll();

        return self::SUCCESS;
    }
}
