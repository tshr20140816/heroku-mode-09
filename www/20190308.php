<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

/*
error_log($mu->get_encrypt_string(base64_decode(getenv('OPENDRIVE_USER'))));
error_log($mu->get_encrypt_string(base64_decode(getenv('OPENDRIVE_PASSWORD'))));
*/

/*
error_log(base64_encode($mu->get_env('OPENDRIVE_USER', true)));
error_log(getenv('OPENDRIVE_USER'));
error_log(base64_encode($mu->get_env('OPENDRIVE_PASSWORD', true)));
error_log(getenv('OPENDRIVE_PASSWORD'));
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
    $tmp = str_replace('&middot;', '', $tmp[0]);
    $rc = preg_match_all('/<li .+?>(.+?)<a/s', $tmp, $matches);
    
    $version_latest = '';
    foreach ($matches[1] as $match) {
        error_log(str_replace('  ', ' ', strip_tags($match)));
        $version_latest .= str_replace('  ', ' ', strip_tags($match)) . "\n";
    }

    $pdo = $mu_->get_pdo();
    $version_current = '';
    foreach ($pdo->query('SELECT version();') as $row) {
        $version_current = $row[0];
    }
    $pdo = null;

    $content = "\nPostgreSQL Version\nlatest : ${version_latest}\ncurrent : ${version_current}\n";
    error_log($content);
    // file_put_contents($file_name_blog_, $content, FILE_APPEND);
}
