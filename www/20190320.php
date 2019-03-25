<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
$access_token = $mu->get_access_token();
$rc = func_test3($mu, '/tmp/dummy');
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');


function func_test3($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $folder_id_private = $mu_->get_folder_id('PRIVATE');
    $list_context_id = $mu_->get_contexts();
    $list_add_task = [];

    $yyyy = date('Y');
    $ymd = date('Ymd', strtotime('+9 hours'));
    for ($i = 3; $i < 10; $i++) {
        $url = "https://elevensports.jp/schedule/farm/${yyyy}/" . str_pad($i, 2, '0', STR_PAD_LEFT) . "?4nocache${ymd}";
        $res = $mu_->get_contents($url, null, true);

        $rc = preg_match_all('/<tr>(.+?)<\/tr>/s', $res, $matches);

        foreach ($matches[1] as $item) {
            if (mb_strpos($item, '広島') === false) {
                continue;
            }
            $rc = preg_match('/<.+?>(\d+)\/(\d+).+?>' . str_repeat('.*?<.+?>(.+?)<.+?>', 5) . '/s', $item, $match);

            $timestamp = strtotime($yyyy . '/' . $match[1] . '/' . $match[2]);
            if ($timestamp < time()) {
                continue;
            }

            $title = $match[1] . '/' . $match[2] . ' ' . $match[3] . ' ファーム中継 ' . $match[4] . ' v ' . $match[6] . ' ' . $match[7];
            $hash = date('Ymd', $timestamp) . hash('sha512', $title);

            $list_add_task[$hash] = '{"title":"' . $title
                . '","duedate":"' . $timestamp
                . '","context":"' . $list_context_id[date('w', $timestamp)]
                . '","tag":"CARP","folder":"' . $folder_id_private . '"}';
        }
    }
    $count_task = count($list_add_task);

    file_put_contents($file_name_blog_, "Firm Task Add : ${count_task}\n", FILE_APPEND);
    error_log($log_prefix . 'Tasks Firm : ' . print_r($list_add_task, true));
    return $list_add_task;
}
