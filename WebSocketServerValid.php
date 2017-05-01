<?php

class WebSocketServerValid
{
    private $_serv;
    public $key = '^manks.top&swoole$';

    public function __construct()
    {
        $this->_serv = new swoole_websocket_server("127.0.0.1", 9501);
        $this->_serv->set([
            'worker_num' => 1,
            'heartbeat_check_interval' => 30,
            'heartbeat_idle_time' => 62,
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
        $this->checkAccess($serv, $request);
    }

    /**
     * @param $serv
     * @param $frame
     */
    public function onMessage($serv, $frame)
    {
        $this->_serv->push($frame->fd, 'Server: ' . $frame->data);
    }
    public function onClose($serv, $fd)
    {
        echo "client {$fd} closed.\n";
    }

    /**
     * 校验客户端连接的合法性,无效的连接不允许连接
     * @param $serv
     * @param $request
     * @return mixed
     */
    public function checkAccess($serv, $request)
    {
        // get不存在或者uid和token有一项不存在，关闭当前连接
        if (!isset($request->get) || !isset($request->get['uid']) || !isset($request->get['token'])) {
            $this->_serv->close($request->fd);
            return false;
        }
        $uid = $request->get['uid'];
        $token = $request->get['token'];
        // 校验token是否正确,无效关闭连接
        if (md5(md5($uid) . $this->key) != $token) {
            $this->_serv->close($request->fd);
            return false;
        }
    }

    public function start()
    {
        $this->_serv->start();
    }
}

$server = new WebSocketServerValid;
$server->start();