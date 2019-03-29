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
    
    $file_name_ = '/tmp/dummy1.txt';
    file_put_contents($file_name_, 'DUMMY');
    $fh = fopen($file_name_, 'r');
    
    $user_4shared = getenv('4SHARED_USER');
    $password_4shared = getenv('4SHARED_PASSWORD');
    
    $url = 'https://webdav.4shared.com/' . pathinfo($file_name_)['basename'];;
    
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "${user_4shared}:${password_4shared}",
        CURLOPT_PUT => true,
        CURLOPT_INFILE => $fh,
        CURLOPT_INFILESIZE => $file_size,
        CURLOPT_HEADER => true,
    ];
    
    $res = $mu_->get_contents($url, $options);
    
    error_log($res);
    
    fclose($fh);
    unlink($file_name_);
}
