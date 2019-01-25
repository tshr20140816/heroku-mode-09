<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'http://www.carp.co.jp/_calendar/list.html';

$res = $mu->get_contents($url, null, true);

$rc = preg_match_all('/<tr.*?><td.*?>(.+?);(.+?)<.+?><.+?>.*?<.+?><.+?>(.+?)<\/td><.+?>(.+?)</s', $res, $matches,  PREG_SET_ORDER);

error_log(print_r($matches, true));

foreach($matches as $item) {
    $item[1] = '2019/' . mb_substr($item[1], 0, 2) . '/' . mb_substr($item[1], 3, 2);
    $item[3] = trim(strip_tags($item[3]));
    $title = mb_substr($item[1], 0, 2) . '/' . mb_substr($item[1], 3, 2) . ' ' . $item[2] . ' ' . $item[3] . ' ' . $item[4];
    // array_shift($item);
    // error_log(print_r($item, true));
    error_log($title);
}
