<?php

$rc = opcache_compile_file('./hourly.php');

error_log($rc);

$res = [];

exec('ls', $res);

error_log(print_r($res, true));
