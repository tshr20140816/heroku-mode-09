<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190408($mu, '/tmp/dummy');

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190408($mu_, $file_name_blog_)
{
    $url = getenv('URL_YOUTUBE');
    $url = str_replace('https://www.', 'https://m.', $url);
    $options = [CURLOPT_USERAGENT => 'Mozilla/5.0 (Linux; Android 9; Pixel 3 Build/PQ1A.181105.013) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Mobile Safari/537.36'];

    $res = $mu_->get_contents($url, $options);
    error_log($res);
}
