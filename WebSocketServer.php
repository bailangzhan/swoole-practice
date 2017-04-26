<?php

class WebSocketServer
{
    private $_serv;

    public function __construct()
    {
        $this->_serv = new swoole_websocket_server("127.0.0.1", 9501);
        $this->_serv->set([
            'worker_num' => 2,
        ]);
        $this->_serv->on('open', [$this, 'onOpen']);
        $this->_serv->on('message', [$this, 'onMessage']);
        $this->_serv->on('close', [$this, 'onClose']);
    }

    /**
     * @param $serv
     * @param $request
     */
    public function onOpen($serv, $request)
    {
        echo "server: handshake success with fd{$request->fd}.\n";
    }

    /**
     * @param $serv
     * @param $frame
     */
    public function onMessage($serv, $frame)
    {
        // 循环当前的所有连接，并把接收到的客户端信息全部发送
        foreach ($serv->connections as $fd) {
            $serv->push($fd, $frame->data);
        }
    }
    public function onClose($serv, $fd)
    {
        echo "client {$fd} closed.\n";
    }

    public function start()
    {
        $this->_serv->start();
    }
}

$server = new WebSocketServer;
$server->start();