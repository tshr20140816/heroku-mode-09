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
    
    $urls[] = getenv('TEST_URL_100');
    $urls[] = getenv('TEST_URL_101');
    foreach ($urls as $url) {
        $res = $mu_->get_contents($url);

        //error_log($res);

        $rc = preg_match('/<div id="area">.*?<p class="mark">(.*?)<.*?<span class="bstop_name" itemprop="name">(.+?)<.*?itemprop="alternateName">(.*?)<div itemprop="geo"/s', $res, $match);
        array_shift($match);
        error_log(print_r($match, true));
    }
}
