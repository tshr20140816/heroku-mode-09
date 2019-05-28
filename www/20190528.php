<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190528($mu);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190528($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $parameters = ['format' => 'json',
                   'checkinDate' => '2019-06-22',
                   'checkoutDate' => '2019-06-23',
                   'latitude' => '128440.51',
                   'longitude' => '503172.21',
                   'searchRadius' => '1',
                   'applicationId' => getenv('RAKUTEN_APPLICATION_ID'),
                  ];
                   
    
    $url = 'https://app.rakuten.co.jp/services/api/Travel/VacantHotelSearch/20170426?' . http_build_query($parameters);
    
    $res = $mu_->get_contents($url);
    
    error_log(print_r(json_decode($res), true));
}
