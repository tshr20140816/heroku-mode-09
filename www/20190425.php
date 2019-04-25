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

    if (getenv('HEROKU_API_KEY_' . $target_) == '') {
        $api_key = base64_decode(getenv('HEROKU_API_KEY'));
    } else {
        $api_key = base64_decode(getenv('HEROKU_API_KEY_' . $target_));
    }
    error_log($log_prefix . 'A : ' . $api_key);
}
