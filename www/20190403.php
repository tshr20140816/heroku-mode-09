<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

check_cloudapp_usage($mu, '/tmp/dummy');

function check_cloudapp_usage($mu_, $file_name_blog_)
{
    $user_cloudapp = $mu_->get_env('CLOUDAPP_USER', true);
    $password_cloudapp = $mu_->get_env('CLOUDAPP_PASSWORD', true);
    
    $size = 0;
    $view_counter = 0;
    for (;;) {
        $page++;
        $url = 'http://my.cl.ly/items?per_page=100&page=' . $page;
        $options = [
            CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
            CURLOPT_USERPWD => "${user_cloudapp}:${password_cloudapp}",
            CURLOPT_HTTPHEADER => ['Accept: application/json',],
        ];
        $res = $mu_->get_contents($url, $options);
        $json = json_decode($res);
        if (count($json) === 0) {
            break;
        }
        foreach ($json as $item) {
            $size += $item->content_length;
            $view_counter += $item->view_counter;
        }
    }
    
    $size = number_format($size);
    error_log($log_prefix . "CloudApp usage : ${size}Byte ${view_counter}View");
    file_put_contents($file_name_blog_, "\nCloudApp usage : ${size}Byte ${view_counter}View\n\n", FILE_APPEND);
}
