<?php

$http = new swoole_http_server("127.0.0.1", 8000);
$http->on('request', function (swoole_http_request $request, swoole_http_response $response) {
    print_r($request);
    $response->status(200);
    $response->end('hello world.');
});
$http->start();