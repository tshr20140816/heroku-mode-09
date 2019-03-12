<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

error_log($mu->get_encrypt_string(base64_decode(getenv('TERACLOUD_API_KEY'))));
error_log($mu->get_encrypt_string(base64_decode(getenv('TERACLOUD_NODE'))));
error_log($mu->get_encrypt_string(base64_decode(getenv('TERACLOUD_PASSWORD'))));
error_log($mu->get_encrypt_string(base64_decode(getenv('TERACLOUD_USER'))));

/*
error_log(base64_encode($mu->get_env('WORDPRESS_PASSWORD', true)));
error_log(getenv('WORDPRESS_PASSWORD'));
error_log(base64_encode($mu->get_env('WORDPRESS_USERNAME', true)));
error_log(getenv('WORDPRESS_USERNAME'));
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
