<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

get_task_f12($mu, '/tmp/dummy');

function get_task_f12($mu_, $file_name_blog_)
{
    $url = 'https://otn.fujitv.co.jp/json/basic_data/918200222.json';
    
    $res = $mu_->get_contents($url);
    
    // error_log($res);
    $json = json_decode($res);
    
    $data = $json->schedule;
    
    foreach ($data as $item) {
        if ($item->liveFlag == '0') {
            continue;
        }
        error_log(print_r($item, true));
    }
}
