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
    
    $url = getenv('TEST_URL_01');
    $basic_user = getenv('BASIC_USER');
    $basic_password = getenv('BASIC_PASSWORD');
    $login_user = getenv('TEST_USER_01');
    $login_password = getenv('TEST_PASSWORD_01');
    $json = '{"op":"login","user":"' . $login_user .'","password":"' . $login_password . '"}';
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${basic_user}:${basic_password}",
        CURLOPT_HTTPHEADER => ['Content-Type: application/json',],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
    ];
    $res = $mu_->get_contents($url, $options);
    //error_log(print_r(json_decode($res), true));
    $data = json_decode($res);
    //error_log($data->content->session_id);
    $session_id = $data->content->session_id;

    /*
    $json = '{"sid":"' . $session_id . '","op":"getCategories","unread_only":false,"enable_nested":false,"include_empty":false}';
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${basic_user}:${basic_password}",
        CURLOPT_HTTPHEADER => ['Content-Type: application/json',],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
    ];
    $res = $mu_->get_contents($url, $options);
    error_log(print_r(json_decode($res), true));
    */
    /*
    $json = '{"sid":"' . $session_id . '","op":"getCounters","output_mode":"f"}';
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${basic_user}:${basic_password}",
        CURLOPT_HTTPHEADER => ['Content-Type: application/json',],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
    ];
    $res = $mu_->get_contents($url, $options);
    error_log(print_r(json_decode($res), true));
    */
    /*
    $json = '{"sid":"' . $session_id . '","op":"getFeedTree","include_empty":false}';
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${basic_user}:${basic_password}",
        CURLOPT_HTTPHEADER => ['Content-Type: application/json',],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
    ];
    $res = $mu_->get_contents($url, $options);
    //error_log(print_r(json_decode($res), true));
    $data = json_decode($res);
    //error_log(print_r($data->content->categories->items[1], true));
    foreach ($data->content->categories->items as $item) {
        if (strpos($item->id, '-') > 0) {
            continue;
        }
        error_log(print_r($item->items, true));
        foreach ($item->items as $item2) {
            error_log(str_replace('FEED:', '', $item2->id));
        }
    }
    */
    $json = '{"sid":"' . $session_id . '","op":"getHeadlines","feed_id ":4}';
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${basic_user}:${basic_password}",
        CURLOPT_HTTPHEADER => ['Content-Type: application/json',],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
    ];
    $res = $mu_->get_contents($url, $options);
    error_log(print_r(json_decode($res), true));
}
