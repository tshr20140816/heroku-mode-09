<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
$access_token = $mu->get_access_token();

$rc = get_task_f1($mu, '/tmp/dummy');

function get_task_f1($mu_, $file_name_blog_)
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
    $res = $mu_->get_contents($url, null, true);

    $rc = preg_match('/<title>(\d+)/', $res, $match);

    $yyyy = $match[1];

    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $res, $matches);

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
        $item .= ' ⠴⬬⠶⠷⬬⠝ ⚑⚐⚑⚐';

        error_log($yyyy . '/' . substr($item, 0, 5));
        $timestamp = strtotime($yyyy . '/' . substr($item, 0, 5));
        if ($timestamp < time()) {
            continue;
        }
        $tmp = str_replace('__TITLE__', $item, $add_task_template);
        $tmp = str_replace('__DUEDATE__', $timestamp, $tmp);
        $list_add_task[] = str_replace('__CONTEXT__', $list_context_id[date('w', $timestamp)], $tmp);
    }
    $list_add_task = array_unique($list_add_task);

    $count_task = count($list_add_task);
    file_put_contents($file_name_blog_, "F1 Task Add : ${count_task}\n", FILE_APPEND);
    error_log($log_prefix . 'Tasks F1 : ' . print_r($list_add_task, true));
    return $list_add_task;
}
