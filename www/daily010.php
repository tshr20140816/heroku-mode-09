<?php

/*
daily010
→ daily020
  → daily030
  → get_youtube_play_count
    → daily040
      → get_results_batting
*/
include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);

error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'https://' . getenv('HEROKU_APP_NAME') . '.herokuapp.com/daily020.php';
$options = [
    CURLOPT_TIMEOUT => 3,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => getenv('BASIC_USER') . ':' . getenv('BASIC_PASSWORD'),
];
$res = $mu->get_contents($url, $options);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');
