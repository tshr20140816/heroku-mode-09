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

    $yyyy = date('Y');
    $ymd = date('Ymd', strtotime('+9 hours'));
    for ($i = 3; $i < 10; $i++) {
        $url = "https://elevensports.jp/schedule/farm/${yyyy}/" . str_pad($i, 2, '0', STR_PAD_LEFT) . "?4nocache${ymd}";
        $res = $mu_->get_contents($url, null, true);
        
        $rc = preg_match_all('/<tr>(.+?)<\/tr>/s', $res, $matches);
    
        foreach ($matches[1] as $item) {
            if (mb_strpos($item, '広島') === false) {
                continue;
            }
            // error_log($item);
            $rc = preg_match('/<.+?>(\d+)\/(\d+).+?>.*?<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>.*?<.+?>(.+?)<.+?>/s', $item, $match);
            error_log(print_r($match, true));
        }
    }
}
