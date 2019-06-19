<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$rc = func_20190620($mu);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function func_20190620($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'https://www.train-guide.westjr.co.jp/api/v3/sanyo2_st.json';
    
    $res = $mu_->get_contents($url, null, true);
    
    $stations = [];
    foreach (json_decode($res, true)['stations'] as $station) {
        $stations[$station['info']['code']] = $station['info']['name'];
    }
    
    error_log(print_r($stations, true));
    
    $url = 'https://www.train-guide.westjr.co.jp/api/v3/sanyo2.json';
}
    
