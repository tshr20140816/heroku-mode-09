<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$access_token = $mu->get_access_token();

check_version_apache($mu);

$time_finish = microtime(true);
error_log("${pid} FINISH " . ($time_finish - $time_start) . 's ');

exit();

function check_version_apache2($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    // Get Folders
    $folder_id_label = $mu_->get_folder_id('LABEL');
    // Get Contexts
    $list_context_id = $mu_->get_contexts();
    
    $list_add_task = [];
    $add_task_template = '{"title":"__TITLE__","duedate":"__DUEDATE__","context":"__CONTEXT__","tag":"WEATHER2","folder":"'
      . $folder_id_label . '"}';
    
    $y = (int)date('Y') - 1;
    for ($i = 0; $i < 3; $i++) {
        $y++;
        $url = "https://e-moon.net/calendar_list/calendar_moon_${y}/";
        if ($i > 0) {
            $res = $mu_->get_contents($url, [CURLOPT_NOBODY => true]);
            if ($res != '') {
                continue;
            }
        }
        $res = $mu_->get_contents($url);
        
        $pattern = '/<td class="embed_link_to_star_mall_fullmoon">(\d+).+?(\d+).+?(\d+).+?(\d+)(.*?)</s';
        $rc = preg_match_all($pattern, $res, $matches,  PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            for ($j = 1; $j < 5; $j++) {
                $match[$j] = str_pad($match[$j], 2, '0', STR_PAD_LEFT);
            }
            $match[5] = trim($match[5]);
            if ($match[5] == '') {
                $match[5] = '満月';
            }
            $title = $match[1] . '/' . $match[2] . ' ' . $match[3] . ':' . $match[4] . ' ' .  $match[5] . ' ★';
            $timestamp = mktime(0, 0, 0, $match[1], $match[2], $y);
            if ($timestamp < strtotime('+3 days')) {
                continue;
            }
            $hash = date('Ymd', $timestamp) . hash('sha512', $title);
            
            $tmp = str_replace('__TITLE__', $title, $add_task_template);
            $tmp = str_replace('__DUEDATE__', $timestamp, $tmp);
            $tmp = str_replace('__CONTEXT__', $list_context_id[date('w', $timestamp)], $tmp);
            $list_add_task[$hash] = $tmp;
        }
    }
    
    error_log($log_prefix . 'FULL MOON : ' . print_r($list_add_task, true));
    return $list_add_task;
}

function check_version_apache($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    // Get Folders
    $folder_id_label = $mu_->get_folder_id('LABEL');
    // Get Contexts
    $list_context_id = $mu_->get_contexts();
    
    $list_add_task = [];
    $add_task_template = '{"title":"__TITLE__","duedate":"__DUEDATE__","context":"__CONTEXT__","tag":"WEATHER2","folder":"'
      . $folder_id_label . '"}';
    
    $y = date('Y');
    $m = date('m');
    for ($i = 0; $i < 36; $i++) {
        $url = "https://www.nao.ac.jp/astro/sky/${y}/" . str_pad($m, 2, '0', STR_PAD_LEFT) . '.html';
        $res = $mu_->get_contents($url, [CURLOPT_NOBODY => true]);
        if ($res != '') {
            break;
        }
        $res = $mu_->get_contents($url);

        $pattern = '/<tr>.+?<td.*?>(\d+).+?<\/td>.*?<td.*?>(.+?)<\/td>.*?<\/tr>/s';
        $rc = preg_match_all($pattern, $res, $matches,  PREG_SET_ORDER);

        foreach ($matches as $match) {
            $title = str_pad($m, 2, '0', STR_PAD_LEFT) . '/' . str_pad($match[1], 2, '0', STR_PAD_LEFT) . ' ' . mb_convert_kana(strip_tags($match[2]), 'a') . ' ★';
            $timestamp = mktime(0, 0, 0, $m, $match[1], $y);
            
            $hash = date('Ymd', $timestamp) . hash('sha512', $title);
            
            $tmp = str_replace('__TITLE__', $title, $add_task_template);
            $tmp = str_replace('__DUEDATE__', $timestamp, $tmp);
            $tmp = str_replace('__CONTEXT__', $list_context_id[date('w', $timestamp)], $tmp);
            $list_add_task[$hash] = $tmp;
        }
        $m = $m == 12 ? 1 : $m + 1;
        $y = $m == 1 ? $y + 1 : $y;
    }
    
    error_log($log_prefix . 'FULL MOON : ' . print_r($list_add_task, true));
    return $list_add_task;
}
