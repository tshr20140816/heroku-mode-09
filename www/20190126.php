<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'http://www.carp.co.jp/_calendar/list.html';

$res = $mu->get_contents($url);

$rc = preg_match_all('/<tr.*?><td.*?>(.+?);(.+?)<.+?><.+?>.*?<.+?><.+?>(.+?)<\/td><.+?>(.+?)</s', $res, $maches);

array_shift($maches);

error_log(print_r($maches, true));
