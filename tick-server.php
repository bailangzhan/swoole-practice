<?php

$serv = new swoole_server('127.0.0.1', 9501);
$serv->set([
    'worker_num' => 2,
]);
$serv->on('WorkerStart', function ($serv, $workerId){
    if ($workerId == 0) {
        $i = 0;
        $params = 'world';
        $serv->tick(1000, function ($timeId) use ($serv, &$i, $params) {
            $i ++;
            echo "hello, {$params} --- {$i}\n";
            if ($i >= 5) {
                $serv->clearTimer($timeId);
            }
        });
    }
});
$serv->on('Connect', function ($serv, $fd) {
});
$serv->on('Receive', function ($serv, $fd, $fromId, $data) {
    $serv->after(3000, function () {
        echo "only once.\n";
    });
});
$serv->on('Close', function ($serv, $fd) {
});
$serv->start();