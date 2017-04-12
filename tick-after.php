<?php

swoole_timer_after(3000, function () {
    echo "only once.\n";
});