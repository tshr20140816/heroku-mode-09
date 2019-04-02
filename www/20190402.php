<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

get_task_f12($mu, '/tmp/dummy');

function get_task_f12($mu_, $file_name_blog_)
{
    $list_add_task = [];
    $add_task_template = '{"title":"__TITLE__","duedate":"__DUEDATE__","context":"__CONTEXT__","tag":"F1","folder":"'
      . $folder_id_label . '"}';
    
    $url = 'https://otn.fujitv.co.jp/json/basic_data/918200222.json';
    
    $res = $mu_->get_contents($url);
    
    // error_log($res);

    foreach (json_decode($res)->schedule as $item) {
        if ($item->liveFlag == '0') {
            continue;
        }

        $timestamp = strtotime(substr($item->strDateTime, 0, 10));
        if ($timestamp < time()) {
            continue;
        }

        $title = substr($item->strDateTime, 11, 5) . ' ' .  $item->subTitle . ' ⠴⬬⠶⠷⬬⠝ ⚑⚐⚑⚐';

        $tmp = str_replace('__TITLE__', $title, $add_task_template);
        $tmp = str_replace('__DUEDATE__', $timestamp, $tmp);
        $list_add_task[] = str_replace('__CONTEXT__', $list_context_id[date('w', $timestamp)], $tmp);
    }

    $count_task = count($list_add_task);
    file_put_contents($file_name_blog_, "F1 Task Add : ${count_task}\n", FILE_APPEND);
    error_log($log_prefix . 'Tasks F1 : ' . print_r($list_add_task, true));
    // return $list_add_task;
}
