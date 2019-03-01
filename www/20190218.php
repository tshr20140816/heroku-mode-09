<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'https://www.ticket.carp.co.jp/ticket/official-general/admission/login?gameIndex=0&seatIndex=7';

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

for ($i = 0; $i < 100; $i++) {
    $res = $mu->get_contents($url, $options);
    if (strlen($res) > 100) {
        echo $res;
        break;
    }
}

error_log($res);
