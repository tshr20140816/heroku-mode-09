<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$user_teracloud = base64_decode(getenv('TERACLOUD_USER'));
$password_teracloud = base64_decode(getenv('TERACLOUD_PASSWORD'));
$api_key_teracloud = base64_decode(getenv('TERACLOUD_API_KEY'));
$node_teracloud = base64_decode(getenv('TERACLOUD_NODE'));

$url = "https://${node_teracloud}.teracloud.jp/v2/api/dataset/(property)";

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => "${user_teracloud}:${password_teracloud}",
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_HTTPHEADER => ['X-TeraCLOUD-API-KEY: ' . $api_key_teracloud,],
];
$res = $mu->get_contents($url, $options);
error_log($res);

$data = json_decode($res);
error_log(print_r($data, true));

/*
$file_name = '/tmp/test.txt';

file_put_contents($file_name, 'TEST');

$file_size = filesize($file_name);
$fh = fopen($file_name, 'r');

$url = "https://${node_teracloud}.teracloud.jp/dav/" . pathinfo($file_name)['basename'];

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
*/
