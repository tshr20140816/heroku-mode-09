<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

func_20190403($mu, '/tmp/dummy');

function func_20190403($mu_, $file_name_blog_)
{
    $user_cloudapp = $this->get_env('CLOUDAPP_USER', true);
    $password_cloudapp = $this->get_env('CLOUDAPP_PASSWORD', true);
    
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
            error_log(print_r($item, true));
        }
    }
}
