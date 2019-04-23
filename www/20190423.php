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
    
    // $mu_->post_blog_hatena('TEST', '<div class="0123456789abcdefghijklmnopqrstuvwxyz+/=">TEST</div>');
    // $title = 'toddledo quota';
    /*
    $title = 'upeemfeprvpub';
    $description = '<div class="upeemfeprvpub">11163 33000 32293 31560 31560 30260 29577 28858 28858 28858 26680 26015 25392 24742 24121 23431 23431 22006 21356 20649 19927 19303 18661</div>';
    $mu_->post_blog_hatena($title, $description);
    */
    
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
