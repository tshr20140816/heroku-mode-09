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
    
    $list_info = [];
    
    for ($i = 0; $i < 10; $i++) {
        $info = '';
        $url = $mu_->get_env('URL_RAKUTEN_TRAVEL_0' . $i);
        if (strlen($url) < 10) {
            continue;
        }

        // $tmp = explode('&', parse_url($url, PHP_URL_QUERY));
        parse_str(parse_url($url, PHP_URL_QUERY), $tmp);
        error_log(print_r($tmp, true));

        $y = $tmp['f_nen1'];
        $m = $tmp['f_tuki1'];
        $d = $tmp['f_hi1'];

        $info = "${y}/${m}/${d}\r\n";

        $res = $mu_->get_contents_proxy($url);

        $tmp = explode('<dl class="htlGnrlInfo">', $res);
        array_shift($tmp);

        foreach ($tmp as $hotel_info) {
            $rc = preg_match('/<a id.+>(.+?)</', $hotel_info, $match);
            error_log($match[1]);
            $info .= $match[1];
            $rc = preg_match('/<span class="vPrice".*?>(.+)/', $hotel_info, $match);
            error_log(strip_tags($match[1]));
            $info .= ' ' . strip_tags($match[1]) . "\r\n";
        }
        error_log($info);
        $list_info[$y . $m . $d] = $info;
    }
    ksort($list_info);
    error_log(print_r($list_info, true));
}
