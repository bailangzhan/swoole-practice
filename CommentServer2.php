<?php

class CommentServer2
{
    private $_serv;
    public $key = '^manks.top&swoole$';
    // 用户id和fd对应的映射,key => value,key是用户的uid,value是用户的fd
    public $user2fd = [];
    private $_tcp;

    public function __construct()
    {
        $this->_serv = new swoole_websocket_server("127.0.0.1", 9501);
        $this->_serv->set([
            'worker_num' => 1,
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time' => 125,
        ]);

        $this->_tcp = $this->_serv->listen('127.0.0.1', 9502, SWOOLE_SOCK_TCP);
        $this->_tcp->set([
            'open_eof_check' => true, //打开EOF检测
            'package_eof' => "\r\n", //设置EOF
            'open_eof_split' => true, // 自动分包
        ]);
        $this->_tcp->on('Receive', [$this, 'onReceive']);

        $this->_serv->on('open', [$this, 'onOpen']);
        $this->_serv->on('message', [$this, 'onMessage']);
        $this->_serv->on('close', [$this, 'onClose']);
    }

    /**
     * @param $serv
     * @param $request
     * @return mixed
     */
    public function onOpen($serv, $request)
    {
        // 连接授权
        $accessResult = $this->checkAccess($serv, $request);
        if (!$accessResult) {
            return false;
        }
        // 始终把用户最新的fd跟uid映射在一起
        if (array_key_exists($request->get['uid'], $this->user2fd)) {
            $existFd = $this->user2fd[$request->get['uid']];
            $this->close($existFd, 'uid exists.');
            $this->user2fd[$request->get['uid']] = $request->fd;
            return false;
        } else {
            $this->user2fd[$request->get['uid']] = $request->fd;
        }
    }

    /**
     * @param $serv
     * @param $frame
     * @return mixed
     */
    public function onMessage($serv, $frame)
    {
        // 校验数据的有效性，我们认为数据被`json_decode`处理之后是数组并且数组的`event`项非空才是有效数据
        // 非有效数据，关闭该连接
        $data = $frame->data;
        $data = json_decode($data, true);
        if (!$data || !is_array($data) || empty($data['event'])) {
            $this->close($frame->fd, 'data format invalidate.');
            return false;
        }
        // 根据数据的`event`项，判断要做什么,`event`映射到当前类具体的某一个方法，方法不存在则关闭连接
        $method = $data['event'];
        if (!method_exists($this, $method)) {
            $this->close($frame->fd, 'event is not exists.');
            return false;
        }
        $this->$method($frame->fd, $data);
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
            $this->close($request->fd, 'access faild.');
            return false;
        }
        $uid = $request->get['uid'];
        $token = $request->get['token'];
        // 校验token是否正确,无效关闭连接
        if (md5(md5($uid) . $this->key) != $token) {
            $this->close($request->fd, 'token invalidate.');
            return false;
        }
        return true;
    }

    /**
     * @param $fd
     * @param $message
     * 关闭$fd的连接，并删除该用户的映射
     */
    public function close($fd, $message = '')
    {
        // 关闭连接
        $this->_serv->close($fd);
        // 删除映射关系
        if ($uid = array_search($fd, $this->user2fd)) {
            unset($this->user2fd[$uid]);
        }
    }

    public function alertTip($fd, $data)
    {
        // 推送目标用户的uid非真或者该uid尚无保存的映射fd，关闭连接
        if (empty($data['toUid']) || !array_key_exists($data['toUid'], $this->user2fd)) {
            $this->close($fd);
            return false;
        }
        $this->push($this->user2fd[$data['toUid']], ['event' => $data['event'], 'msg' => '收到一条新的回复.']);
    }
    /**
     * @param $fd
     * @param $message
     */
    public function push($fd, $message)
    {
        if (!is_array($message)) {
            $message = [$message];
        }
        $message = json_encode($message);
        // push失败，close
        if ($this->_serv->push($fd, $message) == false) {
            $this->close($fd);
        }
    }

    public function start()
    {
        $this->_serv->start();
    }

    /**
     * =============== TCP SERVER OPERATE ============================================
     */
    /**
     * tcp server register function onReceive
     */
    public function onReceive($serv, $fd, $fromId, $data)
    {
        try {
            $data = json_decode($data, true);
            if (!isset($data['event'])) {
                throw new \Exception("params error, needs event param.", 1);
            }
            
            $method = $data['event'];

            // 调起对应的方法
            if(!method_exists($this, $method)) {
                throw new \Exception("params error, not support method.", 1);
            }
            $this->$method($fd, $data);

            return true;

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            throw new \Exception("{$msg}", 1);
        }
    }
}

$server = new CommentServer2;
$server->start();