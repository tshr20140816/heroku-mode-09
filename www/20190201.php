<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);

error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

check_bus($mu);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function check_bus($mu_) {
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $cookie = $tmpfname = tempnam("/tmp", time());
    
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
    ];
    
    $urls[] = getenv('TEST_URL_100');
    $urls[] = getenv('TEST_URL_101');
    $urls[] = getenv('TEST_URL_102');
    $urls[] = getenv('TEST_URL_103');
    $urls[] = getenv('TEST_URL_104');
    $urls[] = getenv('TEST_URL_105');
    foreach ($urls as $url) {
        $res = $mu_->get_contents($url, $options);

        //error_log($res);

        $rc = preg_match('/<title>(.+?)</s', $res, $match);
        //array_shift($match);
        error_log(print_r($match, true));
    }
    
    unlink($cookie);
}
