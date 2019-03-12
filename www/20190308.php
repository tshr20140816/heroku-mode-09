<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

/*
error_log($mu->get_encrypt_string(base64_decode(getenv('TERACLOUD_API_KEY'))));
error_log($mu->get_encrypt_string(base64_decode(getenv('TERACLOUD_NODE'))));
error_log($mu->get_encrypt_string(base64_decode(getenv('TERACLOUD_PASSWORD'))));
error_log($mu->get_encrypt_string(base64_decode(getenv('TERACLOUD_USER'))));
*/

/*
error_log(base64_encode($mu->get_env('TERACLOUD_API_KEY', true)));
error_log(getenv('TERACLOUD_API_KEY'));
error_log(base64_encode($mu->get_env('TERACLOUD_NODE', true)));
error_log(getenv('TERACLOUD_NODE'));
error_log(base64_encode($mu->get_env('TERACLOUD_PASSWORD', true)));
error_log(getenv('TERACLOUD_PASSWORD'));
error_log(base64_encode($mu->get_env('TERACLOUD_USER', true)));
error_log(getenv('TERACLOUD_USER'));
*/

/*
$user_cloudme = getenv('CLOUDME_USER');
$password_cloudme = getenv('CLOUDME_PASSWORD');

$url = "https://webdav.cloudme.com/${user_cloudme}";
$url = "https://webdav.4shared.com";

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_HEADER => true,
];

$res = $mu->get_contents($url, $options);

error_log($res);
*/

check_version_postgresql($mu, '/tmp/dummy');

function check_version_postgresql($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'https://www.postgresql.org/?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);

    $tmp = explode('<h2>Latest Releases</h2>', $res);
    $tmp = explode('</ul>', $tmp[1]);
    $rc = preg_match_all('/<li>(.+?)<a/s', $tmp[0], $matches,  PREG_SET_ORDER);
    error_log(print_r($matches, true));
    $version_latest = '';

    // error_log($log_prefix . '$version_latest : ' . $version_latest);
    // error_log($log_prefix . '$version_current : ' . $version_current);

    $content = "\nPostgreSQL Version\nlatest : ${version_latest}\ncurrent : ${version_current}\n";
    // file_put_contents($file_name_blog_, $content, FILE_APPEND);
}
