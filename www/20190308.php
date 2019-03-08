<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$user_cloudme = getenv('CLOUDME_USER');
$user_password = getenv('CLOUDME_PASSWORD');

$url = 'https://webdav.cloudme.com/${user_cloudme}';

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => "${user_cloudme}:${user_password}",
];

$res = $mu->get_contents($url, $options);

error_log($res);
