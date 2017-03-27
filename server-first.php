<?php

$serv = new swoole_server('127.0.0.1', 9501);

$serv->set([
    'worker_num' => 2,
]);

// 有新的客户端连接时，worker进程内会触发该回调
$serv->on('Connect', function ($serv, $fd) {
    echo "new client connected." . PHP_EOL;
});

// server接收到客户端的数据后，worker进程内触发该回调
$serv->on('Receive', function ($serv, $fd, $fromId, $data) {
    // 收到数据后发送给客户端
    $serv->send($fd, 'Server '. $data);
});

// 客户端断开连接或者server主动关闭连接时 worker进程内调用
$serv->on('Close', function ($serv, $fd) {
    echo "Client close." . PHP_EOL;
});

// 启动server
$serv->start();