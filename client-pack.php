<?php

$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
$client->connect('127.0.0.1', 9501) || exit("connect failed. Error: {$client->errCode}\n");

// 向服务端发送数据
for ($i = 0; $i < 3; $i++) {
    $data = "Just a test.";
    $data = pack('N', strlen($data)) . $data;
    $client->send($data);
}

$client->close();