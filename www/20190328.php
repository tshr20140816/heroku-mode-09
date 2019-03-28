<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
$rc = func_test20190328($mu, '/tmp/dummy');
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test20190328($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $username = $mu_->get_env('WORDPRESS_USERNAME', true);
    $password = $mu_->get_env('WORDPRESS_PASSWORD', true);
    $clientid = getenv('WORDPRESS_CLIENT_ID');
    
    $url = 'https://public-api.wordpress.com/oauth2/token';
    $post_data = ['client_id' => $clientid,
                  'client_secret' => getenv('WORDPRESS_CLIENT_SECRET'),
                  'grant_type' => 'password',
                  'username' => $username,
                  'password' => $password,
                 ];
    
    $options = [CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($post_data),
               ];
    $res = $mu_->get_contents($url, $options);
    
    error_log(print_r(json_decode($res), true));
    
    $access_token = json_decode($res)->access_token;
    
    $url = "https://public-api.wordpress.com/oauth2/token-info?client_id=${clientid}&token=" . urlencode($access_token);
    $res = $mu_->get_contents($url);
    
    error_log(print_r(json_decode($res), true));
    
    $url = 'https://public-api.wordpress.com/rest/v1/me/';
    $options = [CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token,]];
    $res = $mu_->get_contents($url, $options);
    error_log(print_r(json_decode($res), true));
}
