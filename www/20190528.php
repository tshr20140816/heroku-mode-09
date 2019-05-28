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
    
    /*
    $parameters = ['format' => 'json',
                   'checkinDate' => '2019-10-19',
                   'checkoutDate' => '2019-10-19',
                   'datumType' => '1',
                   'latitude' => '36.230672',
                   'longitude' => '137.963916',
                   'searchRadius' => '3',
                   'allReturnFlag' => '1',
                   'applicationId' => getenv('RAKUTEN_APPLICATION_ID'),
                  ];
                   
    
    $url = 'https://app.rakuten.co.jp/services/api/Travel/VacantHotelSearch/20170426?' . http_build_query($parameters);
    
    $res = $mu_->get_contents($url);
    
    error_log(print_r(json_decode($res), true));
    
    foreach (json_decode($res)->hotels as $one_record) {
        error_log($one_record->hotel[0]->hotelBasicInfo->hotelName);
        error_log($one_record->hotel[0]->hotelBasicInfo->hotelMinCharge);
        error_log($one_record->hotel[0]->hotelBasicInfo->reviewCount);
        error_log($one_record->hotel[0]->hotelBasicInfo->reviewAverage);
    }
    */
    $parameters = ['key' => getenv('JALAN_APPLICATION_ID'),
                   's_area' => '162202',
                   'adult_num' => '2',
                   'count' => '100',
                   'xml_ptn' => '2',
                  ];
    $url = 'http://jws.jalan.net/APIAdvance/HotelSearch/V1/?' . http_build_query($parameters);
    $res = $mu_->get_contents($url);
    error_log($res);
}
