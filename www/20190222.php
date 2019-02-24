<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_test($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    // Get Folders
    $folder_id_label = $mu_->get_folder_id('LABEL');
    // Get Contexts
    $list_context_id = $mu_->get_contexts();
    
    $list_add_task = [];
    $add_task_template = '{"title":"__TITLE__","duedate":"__DUEDATE__","context":"__CONTEXT__","tag":"F1","folder":"'
      . $folder_id_label . '"}';
    
    $url = 'http://otn.fujitv.co.jp/b_hp/918200222.html';
    $res = $mu_->get_contents($url);
    
    $rc = preg_match('/<title>(\d+)/', $res, $match);
    
    error_log($log_prefix . print_r($match, true));
    $yyyy = $match[1];
    
    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $res, $matches);
    
    // error_log(print_r($matches, true));
    foreach ($matches[1] as $item) {
        if (strpos($item, '生放送') === false) {
            continue;
        }
        $item = str_replace('生放送', '', $item);
        $item = str_replace('2ヵ国語', '', $item);
        $item = str_replace('新番組', '', $item);
        $item = str_replace('～', '-', $item);
        $item = preg_replace('/\s+/s', ' ', strip_tags($item));
        $item = trim(preg_replace('/\(.+?\)/', '', $item));
        // error_log($item);
        $timestamp = strtotime($yyyy . '/' . substr($item, 0, 5));
        if ($timestamp < time()) {
            continue;
        }
        $tmp = str_replace('__TITLE__', $item, $add_task_template);
        $tmp = str_replace('__DUEDATE__', $timestamp, $tmp);
        $list_add_task[] = str_replace('__CONTEXT__', $list_context_id[date('w', $timestamp)], $tmp);
    }
    $list_add_task = array_unique($list_add_task);
    error_log(print_r($list_add_task, true));
}
