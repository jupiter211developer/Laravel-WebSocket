<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Http\Controllers\WebSocketController;

class WebSocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:init';

     /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializing Websocket server to receive and manage connections';
 
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
     /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $socketController = new WebSocketController();
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $socketController
                )
            ),
            8090
        );

        $server->loop->addPeriodicTimer(5, function () use ($socketController) {
            $send = [
                "message" => "server sent data to clients every second",
                "time" => date("m/d/Y h:i:s a", time())
            ];        
            foreach ($socketController->connections as $client) {                  
                    $client['conn']->send(json_encode($send));          
            }
        });
        $server->loop->run();
        $server->run();
    }
}