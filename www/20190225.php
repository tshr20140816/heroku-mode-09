<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'https://webdav.pcloud.com/';

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_USERPWD => getenv('PCLOUD_USER') . ':' . getenv('PCLOUD_PASSWORD'),
];

$res = $mu->get_contents($url, $options);

error_log($res);

$url = 'https://api.pcloud.com/userinfo?getauth=1&logout=1&username=' . getenv('PCLOUD_USER') . '&password=' . getenv('PCLOUD_PASSWORD');

$res = $mu->get_contents($url);

error_log($res);

$file_name = '/tmp/test.txt';

$url = 'https://webdav.pcloud.com/' . pathinfo($file_name)['basename'];

file_put_contents($file_name, 'TESTDATA');

$file_size = filesize($file_name);
$fh = fopen($file_name, 'r');
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_USERPWD => getenv('PCLOUD_USER') . ':' . getenv('PCLOUD_PASSWORD'),
    CURLOPT_PUT => true,
    CURLOPT_INFILE => $fh,
    CURLOPT_INFILESIZE => $file_size,
];
$res = $mu->get_contents($url, $options);
fclose($fh);
unlink($file_name);

error_log($res);
