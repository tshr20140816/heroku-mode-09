<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
$rc = func_test3($mu, '/tmp/dummy');
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test3($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
        
    $url = 'https://elevensports.jp/schedule/farm/2019/03';
    
    $res = $mu_->get_contents($url);
    
    //error_log($res);
    $rc = preg_match_all('/<tr>(.+?)<\/tr>/s', $res, $matches);
    
    foreach ($matches[1] as $item) {
        if (mb_strpos($item, '広島') === false) {
            continue;
        }
        // error_log($item);
        $rc = preg_match('/<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>/s', $item, $match);
        error_log(print_r($match, true));
    }
}
