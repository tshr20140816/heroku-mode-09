<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_test($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'http://otn.fujitv.co.jp/b_hp/918200222.html';
    $res = $mu_->get_contents($url);
    
    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $res, $matches);
    
    // error_log(print_r($matches, true));
    
    $list_add_task = [];
    foreach ($matches[1] as $item) {
        if (strpos($item, '生放送') === false) {
            continue;
        }
        $item = str_replace('生放送', '', $item);
        $item = str_replace('2ヵ国語', '', $item);
        $item = str_replace('新番組', '', $item);
        $item = str_replace('～', '-', $item);
        $item = preg_replace('/\s+/s', ' ', strip_tags($item));
        $item = trim(preg_replace('/\(.+?\)/', '', $item));
        error_log($item);
        $yyyymmdd = date('Y') . '/' . substr($item, 0, 5);
        error_log($yyyymmdd);
    }
}
