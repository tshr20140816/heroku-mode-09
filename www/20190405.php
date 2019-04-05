<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = getenv('URL_YOUTUBE');
$res = $mu->get_contents($url);

$tmp = explode('window["ytInitialData"] = ', $res);
error_log(strlen($tmp[0]));

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

exit();
