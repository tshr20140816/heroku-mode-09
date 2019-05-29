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
    
    $info = '';
    
    $url = 'https://search.travel.rakuten.co.jp/ds/hotellist/Japan-Mie-Tsu-low?f_nen1=2019&f_tuki1=10&f_hi1=11&f_nen2=2019&f_tuki2=10&f_hi2=12&f_otona_su=2&f_s1=0&f_s2=0&f_y1=0&f_y2=0&f_y3=0&f_y4=0&f_heya_su=1&f_kin2=0&f_ido=0&f_kdo=0&f_km=7.0&f_hyoji=30&f_image=1&f_tab=hotel&f_datumType=WGS&f_point_min=0';
    
    $tmp = explode('&', parse_url($url, PHP_URL_QUERY));
    $y = $tmp['f_nen1'];
    $m = $tmp['f_tuki1'];
    $d = $tmp['f_hi1'];

    $info = "${y}/${m}/${d}";
    
    $res = $mu_->get_contents_proxy($url);
    
    $tmp = explode('<dl class="htlGnrlInfo">', $res);
    array_shift($tmp);

    foreach ($tmp as $hotel_info) {
        $rc = preg_match('/<a id.+>(.+?)</', $hotel_info, $match);
        error_log($match[1]);
        $info .= $match[1];
        $rc = preg_match('/<span class="vPrice".*?>(.+)/', $hotel_info, $match);
        error_log(strip_tags($match[1]));
        $info .= ' ' . $match[1] . "\r\n\r\n";
    }
    error_log($info);
}
