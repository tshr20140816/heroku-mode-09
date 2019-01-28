<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
// $access_token = $mu->get_access_token();

check_uq($mu);

$time_finish = microtime(true);
error_log("${pid} FINISH " . ($time_finish - $time_start) . 's ');

function check_uq($mu_) {
    
    $cookie = $tmpfname = tempnam("/tmp", time());
    
    $url = 'https://my.uqmobile.jp/leo-bs-ptl-web/view/PSYSATH001_90/init/';
    
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
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_TIMEOUT => 20,
    ];
    
    $res = $mu_->get_contents($url, $options);
    
    error_log($res);
}
