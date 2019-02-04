<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// https://webcache.googleusercontent.com/search?q=cache:4GQ-z2i8mrgJ:https://sebastiaandejonge.com/blog/2013/january/22/php-upload-to-webdav-using-curl.html+&cd=1&hl=ja&ct=clnk&gl=jp

$cmd = 'pg_dump --dbname=' . getenv('DATABASE_URL') . ' >/tmp/pg_dump.dat';
exec($cmd);

if (file_exists('/tmp/pg_dump.dat')) {
    error_log(filesize('/tmp/pg_dump.dat'));
}
  
