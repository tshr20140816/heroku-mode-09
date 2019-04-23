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
    
    $url = getenv('URL_100');
    
    $res = $mu_->get_contents($url);
    
    // error_log($res);
    //<rect class="day" width="8" height="8" x="-41" y="10" fill="#c6e48b" data-count="24" data-date="2019-04-22"/>
    $rc = preg_match('/<rect class="day" .+?data-count="(.+?)".+?' . date('Y-m-d', strtotime('-15 hours')) .'/', $res, $match);
    
    error_log(print_r($match, true));
}
