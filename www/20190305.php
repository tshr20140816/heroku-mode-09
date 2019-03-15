<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$rc = func_test($mu, '/tmp/dummy');

function func_test($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $post_data = [
        'access_key' => base64_decode(getenv('ACCESS_KEY')),
        'titile' => 'TEST_TITLE',
        'content' => "TEST_CONTENT\nTWO LINE\nTHREE LINE",
    ];
    $url = 'https://' . getenv('HEROKU_APP_NAME') . '.herokuapp.com/put_blog.php';
    $options = [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => getenv('BASIC_USER') . ':' . getenv('BASIC_PASSWORD'),
    ];
    $res = $mu_->get_contents($url, $options);
}
