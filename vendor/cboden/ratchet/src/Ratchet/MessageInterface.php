<?php
namespace Ratchet;

use Ratchet\Server\IoServer;

interface MessageInterface {
    /**
     * Triggered when a client sends data through the socket
     * @param  \Ratchet\ConnectionInterface $from The socket/connection that sent the message to your application
     * @param  string                       $msg  The message received
     * @param  \Ratchet\Server\IoServer     $server  The Server
     * @throws \Exception
     */
    function onMessage(ConnectionInterface $from, $msg, IoServer $server);
}
