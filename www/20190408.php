<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

func_20190408($mu, '/tmp/dummy');

function func_20190408($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://baseball.yahoo.co.jp/npb/schedule/?date=20190406';
    
    $options = [CURLOPT_HEADER => true,];
    $res = $mu_->get_contents($url, $options);

    error_log($log_prefix . $res);
}
