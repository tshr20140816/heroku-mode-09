<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_test($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $user = base64_decode(getenv('HIDRIVE_USER'));
    $password = base64_decode(getenv('HIDRIVE_PASSWORD'));
    
    $url = "https://webdav.hidrive.strato.com/users/${user}/";
    $options = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "${user}:${password}",
        CURLOPT_HTTPHEADER => ['Connection: keep-alive',],
    ];
    $res = $mu_->get_contents($url, $options);
    
    // error_log($res);
    
    $tmp = explode('<tbody>', $res)[1];
    $rc = preg_match_all('/<a href="(.+?)">/', $tmp, $matches);
    
    array_shift($matches[1]);
    error_log(print_r($matches[1], true));
    
    $size = 0;
    
    foreach($matches[1] as $file_name) {
        $url = "https://webdav.hidrive.strato.com/users/${user}/" . $file_name;

        $options = [
            CURLOPT_HTTPAUTH => CURLAUTH_ANY,
            CURLOPT_USERPWD => "${user}:${password}",
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => ['Connection: keep-alive',],
        ];
        $res = $mu_->get_contents($url, $options);

        $rc = preg_match('/Content-Length: (\d+)/', $res, $match);
        error_log(print_r($match, true));
        $size += (int)$match[1];
    }
    error_log($size);
}

