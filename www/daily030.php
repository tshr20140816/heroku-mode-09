<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = getenv('TEST_URL_01');
$host_name = parse_url($url, PHP_URL_HOST);
$res = $mu->get_contents($url, null, true);

$rc = preg_match_all('/<a class="title" href="(.+?)">/s', $res, $matches);

$urls = [];
foreach ($matches[1] as $item) {
    $url = 'https://' . $host_name . $item;
    $res = $mu->get_contents($url, null, true);
    $rc = substr_count($res, '<item>');
    error_log("${pid} ${rc} ${url}");
    if ($rc == 0) {
        $urls[] = $url;
    }
}

error_log(print_r($urls, true));

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');
