<?php

class ServerPack
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
            'open_length_check'     => true,      // 开启协议解析
            'package_length_type'   => 'N',     // 长度字段的类型
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset'   => 4,       //第几个字节开始计算长度
            'package_max_length'    => 81920,  //协议最大长度
        ]);
        $this->_serv->on('Connect', array($this, 'onConnect'));
        $this->_serv->on('Receive', [$this, 'onReceive']);
        $this->_serv->on('Close', array($this, 'onClose'));
    }
    public function onConnect($serv, $fd, $fromId)
    {
    }
    public function onReceive($serv, $fd, $fromId, $data)
    {
        $info = unpack('N', $data);
        $len = $info[1];
        $body = substr($data, - $len);
        echo "server received data: {$body}\n";
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

$reload = new ServerPack;
$reload->start();