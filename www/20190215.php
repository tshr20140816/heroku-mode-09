<?php

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$res = [];
exec('ls', $res);

$rc = opcache_compile_file('/../classes/MyUtils.php');
error_log("${pid} MyUtils.php : ${rc}");

foreach ($res as $file_name) {
    $rc = opcache_compile_file('./${file_name}');
    error_log("${pid} ${file_name} : ${rc}");
}

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();
