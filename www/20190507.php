<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190507($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190507($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'https://baseball.yahoo.co.jp/npb/standings/';
    $res = $mu_->get_contents($url);
    
    $tmp = explode('<table class="NpbPlSt yjM">', $res);
    // error_log($tmp[1]);
    
    $rc = preg_match_all('/<td>(.+?)</', $tmp[1] . $tmp[2], $matches);
    
    // error_log(print_r($matches, true));
    
    for ($i = 0; $i < 12; $i++) {
        error_log($matches[1][$i * 13 + 7]);
    }
    
    $rc = preg_match_all('/title="(.+?)"/', $tmp[1] . $tmp[2], $matches);
    
    error_log(print_r($matches, true));
}
