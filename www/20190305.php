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

    $url = 'https://member.livedoor.com/login/';
    
    $res = $mu_->get_contents($url);
    
    error_log($res);
    
    $rc = preg_match('/<input type="hidden" name="_token" value="(.+?)" \/>/s', $res, $match);
    
    error_log(print_r($match, true));
    
    $livedoor_id = base64_decode(getenv('LIVEDOOR_ID'));
    $livedoor_password = base64_decode(getenv('LIVEDOOR_PASSWORD'));
    
    $post_data = ['_token' => $match[1], '.next' => '', '.sv' => '', 'livedoor_id' => $livedoor_id, 'password' => $livedoor_password, 'x' => 80, 'y' => 22];
}
