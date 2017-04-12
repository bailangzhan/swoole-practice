<?php

$i = 0;

swoole_timer_tick(1000, function ($timeId, $params) use (&$i) {
    $i ++;
    echo "hello, {$params} --- {$i}\n";
    if ($i >= 5) {
        swoole_timer_clear($timeId);
    }
}, 'world');