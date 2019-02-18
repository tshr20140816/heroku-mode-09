<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'http://www.ticket.carp.co.jp/shop/o_kuuseki/kuuseki.csv';

$options = [
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'DNT: 1',
        'Upgrade-Insecure-Requests: 1',
        ],
    CURLOPT_HEADER => true,
];

$res = $mu->get_contents($url, $options);

// error_log($res);

$file_name = tempnam('/tmp', 'A' . time());

file_put_contents($file_name, $res);

$fp = fopen($file_name, 'r');

while ($one_line = fgets($fp)) {
    if ($one_line == '') {
        break;
    }
    error_log($one_line);
}

fclose($fp);
unlonk($file_name);

