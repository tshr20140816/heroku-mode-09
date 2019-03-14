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
    
    $basic_user = getenv('BASIC_USER');
    $basic_password = getenv('BASIC_PASSWORD');
    
    $options_base = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${basic_user}:${basic_password}",
        CURLOPT_HTTPHEADER => ['Content-Type: application/json',],
        CURLOPT_POST => true,
    ];
    
    $url = getenv('TEST_URL_01');
    $login_user = getenv('TEST_USER_01');
    $login_password = getenv('TEST_PASSWORD_01');
    $json = '{"op":"login","user":"' . $login_user .'","password":"' . $login_password . '"}';
    $options = $options_base + [CURLOPT_POSTFIELDS => $json,];
    $res = $mu_->get_contents($url, $options);
    error_log($res);
    $data = json_decode($res);
    $session_id = $data->content->session_id;

    $livedoor_id = $mu_->get_env('LIVEDOOR_ID', true);
    $url_feed = "http://blog.livedoor.jp/${livedoor_id}/atom.xml";
    
    $json = '{"sid":"' . $session_id . '","op":"getFeeds","cat_id":-3}';
    $options = $options_base + [CURLOPT_POSTFIELDS => $json,];
    $res = $mu_->get_contents($url, $options);
    $data = json_decode($res);
    foreach ($data->content as $feed) {
        // error_log($feed->feed_url);
        // error_log($feed->id);
        if ($url_feed == $feed->feed_url) {
            $json = '{"sid":"' . $session_id . '","op":"updateFeed","feed_id":' . $feed->id . '}';
            $options = $options_base + [CURLOPT_POSTFIELDS => $json,];
            $res = $mu_->get_contents($url, $options);
            error_log(print_r(json_decode($res), true));
        }
    }
}
