<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$cookie = tempnam("/tmp", md5(microtime(true)));

$user_cloudme = getenv('CLOUDME_USER');
$password_cloudme = getenv('CLOUDME_PASSWORD');

$url = "https://webdav.cloudme.com/${user_cloudme}";

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
];

$res = $mu->get_contents($url, $options);

error_log($res);

$file_name = '/tmp/dummy.txt';
file_put_contents($file_name, 'DUMMY');

$file_size = filesize($file_name);
$fh = fopen($file_name, 'r');

$url = "https://webdav.cloudme.com/${user_cloudme}/" . pathinfo($file_name_)['basename'];
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_PUT => true,
    CURLOPT_INFILE => $fh,
    CURLOPT_INFILESIZE => $file_size,
    CURLOPT_HEADER => true,
];
$res = $mu->get_contents($url, $options);

fclose($fh);
@unlink($cookie);
