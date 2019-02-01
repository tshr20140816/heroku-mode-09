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
    ];
    
    $urls[] = getenv('TEST_URL_100');
    /*
    $urls[] = getenv('TEST_URL_101');
    $urls[] = getenv('TEST_URL_102');
    $urls[] = getenv('TEST_URL_103');
    $urls[] = getenv('TEST_URL_104');
    $urls[] = getenv('TEST_URL_105');
    */
    
    $pattern1 = '/<div id="area">.*?<p class="mark">(.*?)<.+?<span class="bstop_name" itemprop="name">(.*?)<.+? itemprop="alternateName">(.*?)</s';
    $pattern2 = '/<p class="time" itemprop="departureTime">\s+(.+?)\s.+?<span class="route">(.*?)<.+?itemprop="name">(.*?)<.+?<\/li>/s';
    foreach ($urls as $url) {
        $res = $mu_->get_contents($url, $options);

        //error_log($res);

        $rc = preg_match($pattern1, $res, $match);
        // array_shift($match);
        // error_log(print_r($match, true));
        $bus_stop_from = $match[2] . ' ' . $match[3] . ' ' .$match[1];
        $bus_stop_from = str_replace('  ', ' ', $bus_stop_from);
        error_log($bus_stop_from);
        
        $rc = preg_match_all($pattern2, $res, $matches,  PREG_SET_ORDER);
        // error_log(print_r($matches, true));
        foreach ($matches as $match) {
            $title[] = str_replace('()', '', $match[1] . ' ' . $bus_stop_from . '→' . $match[3] . '(' . $match[2] . ')');
        }
        error_log(print_r($title, true));
    }
    
    unlink($cookie);
}
