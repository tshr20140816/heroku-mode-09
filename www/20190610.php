<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$rc = func_20190610($mu);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function func_20190610($mu_)
{
    $url = 'https://twitter.com/bs_ponta';
    $res = $mu_->get_contents($url);
    // error_log($res);
    
    $tmp = explode('<div class="js-tweet-text-container">', $res);
    array_shift($tmp);
    // error_log(print_r($tmp, true));
    
    foreach ($tmp as $one_tweet) {
        $rc = preg_match('/<p .+?>(.+?)<.+?<img data-aria-label-part src="(.+?)".+?data-time="(.+?)"/s', $one_tweet, $match);
        error_log(print_r($match, true));
    }
    
}
