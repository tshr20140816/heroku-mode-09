<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190418($mu, '/tmp/dummy.txt');

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190418($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = getenv('TEST_URL_01');
    $res = $mu_->get_contents($url, null, true);
    
    // error_log($res);
    $rc = preg_match_all('/<a class="title" href="(.+?)">/s', $res, $matches);
    // error_log(print_r($matches, true));
    
    error_log(parse_url($url, PHP_URL_HOST));
}
