<?php
namespace App\Http\Controllers;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WebSocketController extends Controller implements MessageComponentInterface{
    public $connections = [];
    
     /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn){
        $this->connections[$conn->resourceId] = compact('conn') + ['user_id' => null];
    }
    
     /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn){
        $disconnectedId = $conn->resourceId;
        unset($this->connections[$disconnectedId]);
        foreach($this->connections as &$connection)
            $connection['conn']->send(json_encode([
                'offline_user' => $disconnectedId,
                'from_user_id' => 'server control',
                'from_resource_id' => null
            ]));
    }
    
     /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e){
        $userId = $this->connections[$conn->resourceId]['user_id'];
        echo "An error has occurred with user $userId: {$e->getMessage()}\n";
        unset($this->connections[$conn->resourceId]);
        $conn->close();
    }
    
     /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $conn The socket/connection that sent the message to your application
     * @param  string $msg The message received
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $conn, $msg){
        $data = json_decode($msg);
        $send = [
            "message" => "server received your message",
            "time" => date("m/d/Y h:i:s a", time())
        ];

        if (isset($data->command)) {
            switch ($data->command) {
                case "chat":
                    $conn->send(json_encode($send));
                break;
            }
        }
    }

    // function sendMessageToClients() {
    //     print("send data to clients every minute");
    //     $send = [
    //         "message" => "server sent data to clients every minute",
    //         "time" => date("m/d/Y h:i:s a", time())
    //     ];
    //     foreach ($this->connections as $conn) {
    //         print("ssss");
    //         $conn->send(json_encode($send));
    //     }
    // }
}