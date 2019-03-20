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

    $user_hidrive = $mu_->get_env('HIDRIVE_USER', true);
    $password_hidrive = $mu_->get_env('HIDRIVE_PASSWORD', true);

    $url = "https://webdav.hidrive.strato.com/users/${user_hidrive}/";
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${user_hidrive}:${password_hidrive}",
        CURLOPT_HTTPHEADER => ['Connection: keep-alive',],
    ];
    $res = $mu_->get_contents($url, $options);

    $tmp = explode('<tbody>', $res)[1];
    $rc = preg_match_all('/<a href="(.+?)">/', $tmp, $matches);

    array_shift($matches[1]);

    $size = 0;
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${user_hidrive}:${password_hidrive}",
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_HTTPHEADER => ['Connection: keep-alive',],
    ];
    foreach ($matches[1] as $file_name) {
        $url = "https://webdav.hidrive.strato.com/users/${user_hidrive}/" . $file_name;
        $urls[$url] = $options;
    }
    $res = $mu_->get_contents_multi($urls, null);

    foreach ($res as $result) {
        $rc = preg_match('/Content-Length: (\d+)/', $result, $match);
        $size += (int)$match[1];
    }
    $percentage = substr(($size / (5 * 1024 * 1024 * 1024)) * 100, 0, 5);
    $size = number_format($size);

    error_log($log_prefix . "HiDrive usage : ${size}Byte ${percentage}%");
    file_put_contents($file_name_blog_, "\nHiDrive usage : ${size}Byte ${percentage}%\n\n", FILE_APPEND);
}
