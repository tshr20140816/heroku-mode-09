<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

error_log(print_r(openssl_get_cipher_methods(), true));

$mu->get_decrypt_string(getenv('YAHOO_API_KEY'));

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
