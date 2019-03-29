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
    
    $user_4shared = $mu_->get_env('4SHARED_USER', true);
    $password_4shared = $mu_->get_env('4SHARED_PASSWORD', true);
    
    $url = 'https://webdav.4shared.com/';
    
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${user_4shared}:${password_4shared}",
        CURLOPT_HEADER => true,
        CURLOPT_CUSTOMREQUEST => 'PROPFIND',        
    ];
    
    $res = $mu_->get_contents($url, $options);
    
    error_log($res);
    
    $rc = preg_match_all('/<D\:getcontentlength>(.+?)<\/D\:getcontentlength>/', $res, $matches);
    //$rc = preg_match_all('/getcontentlength>(.+?)</s', $res, $matches);
    error_log(print_r($matches, true));
    
    /*
    $ftp_link_id = ftp_connect('ftp.4shared.com');
    $rc = ftp_login($ftp_link_id, $user_4shared, $password_4shared);
    error_log('ftp_login : ' . $rc);
    
    $rc = ftp_close($ftp_link_id);
    error_log('ftp_close : ' . $rc);
    */
    
    return;
    
    $file_name_ = '/tmp/dummy1.txt';
    file_put_contents($file_name_, 'DUMMY1');
    
    $file_size = filesize($file_name_);
    $fh = fopen($file_name_, 'r');
    
    $url = 'https://webdav.4shared.com/' . pathinfo($file_name_)['basename'];
    
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
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
    
    $url = 'https://webdav.4shared.com/' . pathinfo($file_name_)['basename'];
    
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${user_4shared}:${password_4shared}",
        CURLOPT_HEADER => true,
    ];
    
    $res = $mu_->get_contents($url, $options);
    
    error_log($res);
}
