<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'https://search.travel.rakuten.co.jp/ds/hotellist/Japan-Mie-Tsu-low?f_nen1=2019&f_tuki1=09&f_hi1=28&f_nen2=2019&f_tuki2=09&f_hi2=29&f_otona_su=2&f_s1=0&f_s2=0&f_y1=0&f_y2=0&f_y3=0&f_y4=0&f_heya_su=1&f_kin2=0&f_ido=0&f_kdo=0&f_km=7.0&f_hyoji=30&f_image=1&f_tab=hotel&f_datumType=WGS&f_point_min=0';

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
];

$res = $mu->get_contents($url, $options);

error_log($res);
