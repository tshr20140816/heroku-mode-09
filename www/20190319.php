<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$rc = func_test($mu, '/tmp/dummy');

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $res = file_get_contents('/tmp/_netblocks.google.com.txt');
    
    error_log($res);
    
    $rc = preg_match_all('/ip4:(.+?) /', $res, $matches);
    
    error_log(print_r($matches, true));
    
    $target_ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    $target_ipaddress = '66.249.90.240';
    $is_google = false;
    foreach ($matches[1] as $cidr) {
        error_log($cidr);
        list($base_ipddress, $subnetmask) = explode('/', $cidr);
        if (ip2long($target_ipaddress) >> (32 - $subnetmask) == ip2long($base_ipddress) >> (32 - $subnetmask)) {
            $is_google = true;
            break;
        }
    }
    
    if ($is_google === true) {
        error_log('OK');
    } else {
        error_log('NG');
    }
}
