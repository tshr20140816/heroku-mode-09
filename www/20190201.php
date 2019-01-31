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
    
    $url = getenv('TEST_URL_100');
    $res = $mu_->get_contents($url);
    
    //error_log($res);
    
    $rc = preg_match_all('/<li.*?>(.+?)<\/li>/s', $res, $matches,  PREG_SET_ORDER);
    
    // error_log(print_r($matches, true));
    
    $pattern = '/<meta itemprop="name" content="(.+?)".+?itemprop="departureTime">(.+?)<.+?itemprop="name">(.+?)</s';
    foreach ($matches as $item) {
        $rc = preg_match($pattern, $item[1], $match);
        if ($rc != 1) {
            continue;
        }
        $match[2] = trim($match[2]);
        array_shift($match);
        error_log(print_r($match, true));
    }
}
