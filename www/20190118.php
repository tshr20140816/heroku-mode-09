<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

check_version_apache($mu);

$time_finish = microtime(true);
error_log("${pid} FINISH " . ($time_finish - $time_start) . 's ');

exit();

function check_version_apache($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://e-moon.net/calendar_list/calendar_moon_2019/';
    $res = $mu_->get_contents($url);
    
    // error_log($res);
    
    $rc = preg_match_all('/<td class="embed_link_to_star_mall_fullmoon">(.+?)</s', $res, $matches,  PREG_SET_ORDER);
    
    error_log(print_r($matches, true));
}

