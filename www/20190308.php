<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

// error_log(print_r(openssl_get_cipher_methods(), true));

$method = 'aes-256-cbc';
$key = getenv('ENCRYPT_KEY');

$iv = hex2bin(substr(hash('sha512', $key), 0, openssl_cipher_iv_length($method) * 2));

$res = openssl_encrypt('TEST_DATA', $method, $key, 0, $iv);
error_log($res);

$res = openssl_decrypt('TEST_DATA', $method, $key, 0, $iv);
error_log($res);

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
