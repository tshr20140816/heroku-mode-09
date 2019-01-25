<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$access_token = $mu->get_access_token();

get_task_carp($mu);

function get_task_carp($mu_) {
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    // Get Folders
    $folder_id_private = $mu_->get_folder_id('PRIVATE');
    
    // Get Contexts
    $list_context_id = $mu_->get_contexts();
    
    $res = $mu->get_contents('http://www.carp.co.jp/_calendar/list.html', null, true);
    $pattern = '/<tr.*?><td.*?>(.+?);(.+?)<.+?><.+?>.*?<.+?><.+?>(.+?)<\/td><.+?>(.+?)</s';
    $rc = preg_match_all($pattern, $res, $matches,  PREG_SET_ORDER);

    $list_add_task = [];

    foreach($matches as $item) {
        if (mb_substr($item[2], 0, 1) == '(') {
            $item[2] = trim($item[2], '()') . ' 予備日';
        }
        $timestamp = strtotime('2019/' . mb_substr($item[1], 0, 2) . '/' . mb_substr($item[1], 3, 2));
        $title = '⚾' . mb_substr($item[1], 0, 2) . '/' . mb_substr($item[1], 3, 2) . ' ' . $item[2] . ' ' . trim(strip_tags($item[3])) . ' ' . $item[4];
        $hash = date('Ymd', $timestamp) . hash('sha512', $title);

        error_log($timestamp . ' '. $title);

        $list_add_task[$hash] = '{"title":"' . $title
          . '","duedate":"' . $timestamp
          . '","context":"' . $list_context_id[date('w', $timestamp)]
          . '","tag":"CARP","folder":"' . $folder_id_private . '"}';
    }
    error_log($log_prefix . 'TASKS CARP : ' . print_r($list_add_task, true));
}
