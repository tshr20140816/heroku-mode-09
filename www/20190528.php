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
    /*
    $parameters = ['key' => getenv('JALAN_API_KEY'),
                   's_area' => '162202',
                   'adult_num' => '2',
                   'count' => '100',
                   'xml_ptn' => '2',
                  ];
    $url = 'http://jws.jalan.net/APIAdvance/HotelSearch/V1/?' . http_build_query($parameters);
    $res = $mu_->get_contents($url);
    error_log($res);
    */
    $cookie = tempnam('/tmp', 'cookie_' . md5(microtime(true)));
    
    $url = 'https://search.travel.rakuten.co.jp/ds/hotellist/Japan-Mie-Tsu-low?f_nen1=2019&f_tuki1=10&f_hi1=11&f_nen2=2019&f_tuki2=10&f_hi2=12&f_otona_su=2&f_s1=0&f_s2=0&f_y1=0&f_y2=0&f_y3=0&f_y4=0&f_heya_su=1&f_kin2=0&f_ido=0&f_kdo=0&f_km=7.0&f_hyoji=30&f_image=1&f_tab=hotel&f_datumType=WGS&f_point_min=0';
    
    $options = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1',
            ],
        // CURLOPT_COOKIEJAR => $cookie,
        // CURLOPT_COOKIEFILE => $cookie,
    ];
    
    $res = $mu_->get_contents($url, $options);
    error_log($res);
}
