<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$rc = apcu_clear_cache();
$mu = new MyUtils();

func_20190331($mu, '/tmp/dummy');

function func_20190331($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $user_cloudapp = getenv('CLOUDAPP_USER');
    $user_cloudpassword = getenv('CLOUDAPP_PASSWORD');
    
    $url = 'https://my.cl.ly/account';
        
    $res = $mu_->get_contents(
        $url,
        [CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
         CURLOPT_USERPWD => "${user_cloudapp}:${user_cloudpassword}",
        ]
    );
    error_log($res);
}
