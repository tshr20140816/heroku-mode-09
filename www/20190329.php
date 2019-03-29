<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

func2019329($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func2019329($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $user_hidrive = $mu_->get_env('HIDRIVE_USER', true);
    $password_hidrive = $mu_->get_env('HIDRIVE_PASSWORD', true);
    
    $url = "https://webdav.hidrive.strato.com/users/${user_hidrive}/";
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${user_hidrive}:${password_hidrive}",
        CURLOPT_CUSTOMREQUEST => 'PROPFIND',
        CURLOPT_HEADER => true,
    ];
    
    $res = $mu_->get_contents($url, $options);
    
    error_log($res);
    
}
