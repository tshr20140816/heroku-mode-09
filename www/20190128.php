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
    $url = 'https://my.uqmobile.jp/leo-bs-ptl-web/view/PSYSATH001_90/init?screenId=PSYSATH001_90&menuType=01';
}
