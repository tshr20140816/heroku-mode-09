<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

get_task_amefootlive($mu, '/tmp/dummy');

function get_task_amefootlive($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    // Get Folders
    $folder_id_label = $mu_->get_folder_id('LABEL');
    // Get Contexts
    $list_context_id = $mu_->get_contexts();

    $list_add_task = [];
    $add_task_template = '{"title":"__TITLE__","duedate":"__DUEDATE__","context":"__CONTEXT__","tag":"AMEFOOT","folder":"'
      . $folder_id_label . '"}';

    $url = 'https://amefootlive.jp/live';
    $res = $mu_->get_contents($url, null, true);

    $pattern = '/<header class="entry-header">.+?<div .+?>.*?(\d+).*?(\d+).*?(\d+).*?(\d+):(\d+)<.+?<h2 .+?><a .+?>(.+?)</s';

    $rc = preg_match_all($pattern, explode('<h1>ライブ予定</h1>', $res)[1], $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        array_shift($match);

        $timestamp = strtotime($match[0] . '-' . $match[1] . '-' . $match[2]);
        if ($timestamp < time()) {
            continue;
        }
        $title = $match[3] . ':' . $match[4] . ' amefootlive ' . $match[5];

        $tmp = str_replace('__TITLE__', $title, $add_task_template);
        $tmp = str_replace('__DUEDATE__', $timestamp, $tmp);

        $list_add_task[] = str_replace('__CONTEXT__', $list_context_id[date('w', $timestamp)], $tmp);
    }

    $count_task = count($list_add_task);
    file_put_contents($file_name_blog_, "Amefoot Task Add : ${count_task}\n", FILE_APPEND);
    error_log($log_prefix . 'Tasks Amefoot : ' . print_r($list_add_task, true));
    return $list_add_task;
}
