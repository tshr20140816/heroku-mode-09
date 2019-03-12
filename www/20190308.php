<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

error_log($mu->get_encrypt_string(base64_decode(getenv('THEOLDREADER_USER'))));
error_log($mu->get_encrypt_string(base64_decode(getenv('THEOLDREADER_PASSWORD'))));

/*
error_log(base64_encode($mu->get_env('OPENDRIVE_USER', true)));
error_log(getenv('OPENDRIVE_USER'));
error_log(base64_encode($mu->get_env('OPENDRIVE_PASSWORD', true)));
error_log(getenv('OPENDRIVE_PASSWORD'));
*/

/*
$user_cloudme = getenv('CLOUDME_USER');
$password_cloudme = getenv('CLOUDME_PASSWORD');

$url = "https://webdav.cloudme.com/${user_cloudme}";
$url = "https://webdav.4shared.com";

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_HEADER => true,
];

$res = $mu->get_contents($url, $options);

error_log($res);
*/
