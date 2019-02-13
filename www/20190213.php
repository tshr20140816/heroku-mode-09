<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$username = base64_decode(getenv('WORDPRESS_USERNAME'));

$url = 'https://inoreader.superfeedr.com/';

$post_data = [
    'hub.mode' => 'subscribe',
    'hub.callback' => 'https://' . $username . '.wordpress.com/',
    'hub.topic' => 'https://' . $username . '.wordpress.com/feed/',
];

$res = $mu->get_contents(
    $url,
    [CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post_data),
    ]
);

error_log($res);
