<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);

error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

if (!isset($_GET['c']) || $_GET['c'] === '' || is_array($_GET['c'])) {
    error_log("${pid} FINISH Invalid Param");
    exit();
}

$count = (int)$_GET['c'];

error_log("COUNT : ${count}");

if ($count !== 0) {
    $count--;
    error_log('SLEEP');
    sleep(5);
    $url = 'https://' . getenv('HEROKU_APP_NAME') . '.herokuapp.com' . $_SERVER['PHP_SELF'] . '?c=' . $count;
    $options = [CURLOPT_TIMEOUT => 3, CURLOPT_USERPWD => getenv('BASIC_USER') . ':' . getenv('BASIC_PASSWORD')];
    $res = $mu->get_contents($url, $options);
} else {
    error_log('OWARI');
}

$time_finish = microtime(true);
// $mu->post_blog_wordpress($requesturi . ' ' . substr(($time_finish - $time_start), 0, 6) . 's');
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

exit();
