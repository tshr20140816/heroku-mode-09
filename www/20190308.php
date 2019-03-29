<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$cookie = tempnam("/tmp", md5(microtime(true)));

$url = "https://webdav.cloudme.com/";

$options = [
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
];

//$res = $mu->get_contents($url, $options);

error_log($res);

$user_cloudme = getenv('CLOUDME_USER');
$password_cloudme = getenv('CLOUDME_PASSWORD');

$url = "https://webdav.cloudme.com/${user_cloudme}/xios/dummy2.txt";

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
];

$res = $mu->get_contents($url, $options);

error_log($res);

$file_name = '/tmp/dummy2.txt';
file_put_contents($file_name, 'DUMMY');

$file_size = filesize($file_name);
$fh = fopen($file_name, 'r');

$url = "https://webdav.cloudme.com/${user_cloudme}/xios/" . pathinfo($file_name)['basename'];
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_PUT => true,
    CURLOPT_INFILE => $fh,
    CURLOPT_INFILESIZE => $file_size,
    CURLOPT_HEADER => true,
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
];
// $res = $mu->get_contents($url, $options);

fclose($fh);
@unlink($cookie);
