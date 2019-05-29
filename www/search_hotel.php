<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

search_hotel($mu);

$time_finish = microtime(true);

$mu->post_blog_wordpress_async("${requesturi} [" . substr(($time_finish - $time_start), 0, 6) . 's]', file_get_contents($file_name_blog));
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

function search_hotel($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $urls = [];
    for ($i = 0; $i < 10; $i++) {
        $url = $mu_->get_env('URL_RAKUTEN_TRAVEL_0' . $i);
        if (strlen($url) < 10) {
            continue;
        }
        $urls[] = $url;
    }
    $results = $mu_->get_contents_proxy_multi($urls);

    foreach ($results as $url => $result) {
        $hash_url = 'url' . hash('sha512', $url);
        error_log($log_prefix . "url hash : ${hash_url}");

        parse_str(parse_url($url, PHP_URL_QUERY), $tmp);

        $y = $tmp['f_nen1'];
        $m = $tmp['f_tuki1'];
        $d = $tmp['f_hi1'];

        $info = "\n\n${y}/${m}/${d}\n";

        $tmp = explode('<dl class="htlGnrlInfo">', $result);
        array_shift($tmp);

        foreach ($tmp as $hotel_info) {
            $rc = preg_match('/<a id.+>(.+?)</', $hotel_info, $match);
            // error_log($match[1]);
            $info .= $match[1];
            $rc = preg_match('/<span class="vPrice".*?>(.+)/', $hotel_info, $match);
            // error_log(strip_tags($match[1]));
            $info .= ' ' . strip_tags($match[1]) . "\n";
        }

        $hash_info = hash('sha512', $info);
        error_log($log_prefix . "info hash : ${hash_info}");

        $res = $mu_->search_blog($hash_url);
        if ($res != $hash_info) {
            $description = '<div class="' . $hash_url . '">' . "${hash_info}</div>${info}";
            $mu_->post_blog_wordpress_async($hash_url, $description);
        }
    }
    $results = null;
}
