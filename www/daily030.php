<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$suffix = '4nocache' . date('Ymd', strtotime('+9 hours'));

$pdo = $mu->get_pdo();
$rc = $pdo->exec('TRUNCATE t_webcache');
error_log($pid . ' TRUNCATE t_webcache $rc : ' . $rc);
$pdo = null;

$url = 'https://' . getenv('HEROKU_APP_NAME') . '.herokuapp.com/daily040.php';
exec('curl -u ' . getenv('BASIC_USER') . ':' . getenv('BASIC_PASSWORD') . " ${url} > /dev/null 2>&1 &");

$url = 'https://baseball.yahoo.co.jp/npb/standings/?' . $suffix;
$urls_is_cache[$url] = null;

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
