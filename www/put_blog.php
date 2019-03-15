<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

if (!isset($_GET['access_key']) || $_GET['access_key'] != base64_decode(getenv('ACCESS_KEY')) || strlen(getenv('ACCESS_KEY')) == 0) {
    error_log("${pid} FINISH Invalid Param");
    exit();
}

$title = $_GET['title'];
$content = $_GET['content'];

if (strlen($title) == 0) {
    error_log("${pid} FINISH Invalid Param");
    exit();
}

$time_finish = microtime(true);
$mu->post_blog_wordpress($title, $content);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
