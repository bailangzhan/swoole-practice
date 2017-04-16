<?php

class TcpBufferServer
{
    private $_serv;

    /**
     * init
     */
    public function __construct()
    {
        $this->_serv = new Swoole\Server("127.0.0.1", 9501);
        $this->_serv->set([
            'worker_num' => 1,
        ]);
        $this->_serv->on('Connect', array($this, 'onConnect'));
        $this->_serv->on('Close', array($this, 'onClose'));
        $this->_serv->on('Receive', [$this, 'onReceive']);
    }
    public function onConnect($serv, $fd, $fromId)
    {
    }
    public function onReceive($serv, $fd, $fromId, $data)
    {
        echo "Server received data: {$data}" . PHP_EOL;
    }
    public function onClose($serv, $fd, $fromId)
    {
    }
    /**
     * start server
     */
    public function start()
    {
        $this->_serv->start();
    }
}

$reload = new TcpBufferServer;
$reload->start();