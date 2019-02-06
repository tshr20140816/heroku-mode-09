<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$filename = 'test.txt';
$filepath = '/tmp/'.$filename;

file_put_contents($filepath, 'DUMMY');

$filesize = filesize($filepath);
$fh = fopen($filepath, 'r');

