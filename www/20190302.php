<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$user_teracloud = base64_decode(getenv('TERACLOUD_USER'));
$password_teracloud = base64_decode(getenv('TERACLOUD_PASSWORD'));

$file_name = '/tmp/test.txt';

file_put_contents($file_name, 'TEST');

$file_size = filesize($file_name);
$fh = fopen($file_name, 'r');

$url = 'https://uno.teracloud.jp/dav/' . pathinfo($file_name)['basename'];

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => "${user_teracloud}:${password_teracloud}",
    CURLOPT_PUT => true,
    CURLOPT_INFILE => $fh,
    CURLOPT_INFILESIZE => $file_size,
];
$res = $mu->get_contents($url, $options);

error_log($res);

fclose($fh);

unlink($file_name);
