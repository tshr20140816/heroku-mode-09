<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// https://webcache.googleusercontent.com/search?q=cache:4GQ-z2i8mrgJ:https://sebastiaandejonge.com/blog/2013/january/22/php-upload-to-webdav-using-curl.html+&cd=1&hl=ja&ct=clnk&gl=jp

$file_name = '/tmp/pg_dump.dat';

$cmd = 'pg_dump --format=plain --dbname=' . getenv('DATABASE_URL') . ' >' . $file_name;
exec($cmd);

error_log('original : ' . filesize($file_name));

$res = openssl_encrypt(file_get_contents($file_name), 'AES256', 'password_dummy', OPENSSL_RAW_DATA, '0123456789012345');

error_log('1 openssl_encrypt : ' . strlen($res));
error_log($res);
$res = bzcompress($res, 9);

error_log('1 bzcompress + openssl_encrypt : ' . strlen($res));

$res = bzcompress(file_get_contents($file_name), 9);

error_log('2 bzcompress : ' . strlen($res));

$res = openssl_encrypt($res, 'AES256', 'password_dummy', OPENSSL_RAW_DATA, '0123456789012345');

error_log('2 bzcompress + openssl_encrypt : ' . strlen($res));


@unlink('/tmp/pg_dump.dat');
