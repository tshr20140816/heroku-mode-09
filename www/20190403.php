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
    for (;;) {
        $page++;
        $url = 'http://my.cl.ly/items?per_page=100&page=' . $page;
        $options = [
            CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
            CURLOPT_USERPWD => "${user_cloudapp}:${password_cloudapp}",
            CURLOPT_HTTPHEADER => ['Accept: application/json',],
        ];
        $res = $this->get_contents($url, $options);
        $json = json_decode($res);
        if (count($json) === 0) {
            break;
        }
        foreach ($json as $item) {
            if ($item->file_name == $base_name) {
                $url_target = $item->href;
                break 2;
            }
        }
    }
}
