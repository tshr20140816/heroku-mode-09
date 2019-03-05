<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

delete_blog_wordpress($mu);

function delete_blog_wordpress($mu) {
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $username = base64_decode(getenv('WORDPRESS_USERNAME'));
    $password = base64_decode(getenv('WORDPRESS_PASSWORD'));
    
    $url = 'https://' . $username . '.wordpress.com/xmlrpc.php';
    
    error_log($log_prefix . 'url : ' . $url);
    $client = XML_RPC2_Client::create($url, ['prefix' => 'wp.']);
    error_log($log_prefix . 'xmlrpc : getUsersBlogs');
    $result = $client->getUsersBlogs($username, $password);
    error_log($log_prefix . 'RESULT : ' . print_r($result, true));
    $blogid = $result[0]['blogid'];
    
    $client = XML_RPC2_Client::create($url, ['prefix' => 'wp.']);
    $result = $client->getPosts($blogid, $username, $password, ['number' => 10, 'orderby' => 'ASC', 'order' => 'date']);
    
    error_log(print_r($result, true));
}
