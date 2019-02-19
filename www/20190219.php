<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$cookie = tempnam("/tmp", time());

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
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
];

$url = 'https://travel.rakuten.co.jp/';

$res = $mu->get_contents($url, $options);

// $url = 'https://search.travel.rakuten.co.jp/ds/hotellist/Japan-Mie-Tsu?f_landmark_id=&f_ido=0&f_kdo=0&f_teikei=&f_disp_type=&f_rm_equip=&f_hyoji=30&f_image=1&f_tab=hotel&f_setubi=&f_point_min=0&f_datumType=&f_cok=&f_hi1=5&f_tuki1=10&f_nen1=2019&f_hi2=6&f_tuki2=10&f_nen2=2019&f_heya_su=1&f_otona_su=2&f_kin2=0&f_kin=&f_s1=0&f_s2=0&f_y1=0&f_y2=0&f_y3=0&f_y4=0';
// $url = 'https://travel.rakuten.co.jp/mie/?lid=topC_map_pref';
$url = 'https://travel.rakuten.co.jp/yado/mie/tsu.html';

$res = $mu->get_contents($url, $options);
// $res = file_get_contents($url);

// error_log($res);

unlink($cookie);
