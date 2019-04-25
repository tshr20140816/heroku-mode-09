<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190425($mu_, '/tmp/dummy');

function func_20190425($mu_, $file_name_blog_, $target_ = 'TOODLEDO')
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $keyword = strtolower($target_);
    for ($i = 0; $i < strlen($keyword); $i++) {
        $keyword[$i] = chr(ord($keyword[$i]) + 1);
    }
    error_log($keyword);
}
