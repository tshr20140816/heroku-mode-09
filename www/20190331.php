<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$rc = apcu_clear_cache();
$mu = new MyUtils();

func_20190331($mu, '/tmp/dummy');

function func_20190331($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://amefootlive.jp/live';
    $res = $mu_->get_contents($url);
    
    error_log($res);
    
    return;
    $pattern = '/' . '<header class="entry-header">.+?<div .+?>(.+?)<.+?<a href="(.+?)"';
    
    $rc = preg_match_all($pattern, explode('<h1>ライブ予定</h1>', $res)[1], $matches);
    
    error_log(print_r($matches, true));
}
