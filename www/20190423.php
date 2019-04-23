<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
func_20190423($mu);
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190423($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
        
    $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
    $url = 'https://' . $hatena_blog_id . '/search?q=upeemfeprvpub';
    $res = $mu_->get_contents($url);
    
    // error_log($res);
    $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);
    
    $res = $mu_->get_contents($match[1]);
    // error_log($res);
    $rc = preg_match('/<div class="upeemfeprvpub">(.+?)</', $res, $match);
    error_log(print_r(explode(' ', $match[1]), true));
}
