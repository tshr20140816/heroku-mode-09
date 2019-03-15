<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
$rc = func_test($mu, '/tmp/dummy');

function func_test($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://www.youtube.com/channel/UCPPC65lyLljwbhAkyEBIQDw/videos';
    
    $res = $mu_->get_contents($url, $options);
    
    // error_log($res);
    $tmp = explode('window["ytInitialData"] = ', $res);
    $tmp = explode('window["ytInitialPlayerResponse"]', $tmp[1]);
    
    //error_log(trim(trim($tmp[0]), ';'));
    //error_log(print_r(json_decode(trim(trim($tmp[0]), ';')), true));
    $json = json_decode(trim(trim($tmp[0]), ';'));
    error_log(print_r($json->contents->twoColumnBrowseResultsRenderer, true));
}
