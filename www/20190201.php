<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);

error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

get_task_bus($mu);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function get_task_bus($mu_) {
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $folder_id_bus = $mu_->get_folder_id('BUS');
    $list_context_id = $mu_->get_contexts();
    $list_add_task = [];
    $timestamp = mktime(0, 0, 0, 1, 1, 2019);
    
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
    
    $urls[] = $mu_->get_env('URL_BUS_01');
    $urls[] = $mu_->get_env('URL_BUS_02');
    $urls[] = $mu_->get_env('URL_BUS_03');
    $urls[] = $mu_->get_env('URL_BUS_04');
    $urls[] = $mu_->get_env('URL_BUS_05');
    $urls[] = $mu_->get_env('URL_BUS_06');
    
    $pattern1 = '/<div id="area">.*?<p class="mark">(.*?)<.+?<span class="bstop_name" itemprop="name">(.*?)<.+? itemprop="alternateName">(.*?)</s';
    $pattern2 = '/<p class="time" itemprop="departureTime">\s+(.+?)\s.+?<span class="route">(.*?)<.+?itemprop="name">(.*?)<.+?<\/li>/s';
    foreach ($urls as $url) {
        $res = $mu_->get_contents($url, $options);

        $rc = preg_match($pattern1, $res, $match);
        
        $bus_stop_from = $match[2] . ' ' . $match[3] . ' ' .$match[1];
        $bus_stop_from = str_replace('  ', ' ', $bus_stop_from);
        error_log($log_prefix . $bus_stop_from);
        
        $rc = preg_match_all($pattern2, $res, $matches,  PREG_SET_ORDER);
        foreach ($matches as $match) {
            $title = str_replace('()', '', $bus_stop_from . ' ' . $match[1] . ' ' . $match[3] . '(' . $match[2] . ')');
            $list_add_task[$hash] = '{"title":"' . $title
                . '","duedate":"' . $timestamp
                . '","context":"' . $list_context_id[date('w', $timestamp)]
                . '","tag":"BUS","folder":"' . $folder_id_bus . '"}';
        }
    }
    $count_task = count($list_add_task);
    // $mu_->post_blog_fc2("BUS Task Add : ${count_task}");

    error_log($log_prefix . 'BUS CARP : ' . print_r($list_add_task, true));
    return $list_add_task;
}
