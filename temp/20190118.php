<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$access_token = $mu->get_access_token();

/*
$list_holiday_name = get_holiday_name($mu);
get_task_sky($mu, $list_holiday_name);
*/
get_task_full_moon($mu);

$time_finish = microtime(true);
error_log("${pid} FINISH " . ($time_finish - $time_start) . 's ');

exit();

function get_task_full_moon($mu_)
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

function get_task_sky($mu_, $list_holiday_name_)
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
        if ($y == date('Y')) {
            $res = $mu_->get_contents($url, null, true);
        } else {
            $res = $mu_->get_contents($url, [CURLOPT_NOBODY => true]);
            if ($res != '') {
                break;
            }
            $res = $mu_->get_contents($url);
        }

        $pattern = '/<tr>.+?<td.*?>(\d+).+?<\/td>.*?<td.*?>(.+?)<\/td>.*?<\/tr>/s';
        $rc = preg_match_all($pattern, $res, $matches,  PREG_SET_ORDER);

        foreach ($matches as $match) {
            $content = mb_convert_kana(strip_tags($match[2]), 'a');
            $title = str_pad($m, 2, '0', STR_PAD_LEFT) . '/' . str_pad($match[1], 2, '0', STR_PAD_LEFT)
                . ' ' . $content . ' ★';
            $timestamp = mktime(0, 0, 0, $m, $match[1], $y);
            if ($timestamp < strtotime('+3 days')) {
                continue;
            }
            if (array_search($content, $list_holiday_name_) != false) {
                continue;
            }
            if (mb_strlen($content) === 2 && $content != '上弦' && $content != '下弦') {
                continue;
            }
            
            $hash = date('Ymd', $timestamp) . hash('sha512', $title);
            
            $tmp = str_replace('__TITLE__', $title, $add_task_template);
            $tmp = str_replace('__DUEDATE__', $timestamp, $tmp);
            $tmp = str_replace('__CONTEXT__', $list_context_id[date('w', $timestamp)], $tmp);
            $list_add_task[$hash] = $tmp;
        }
        $m = $m == 12 ? 1 : $m + 1;
        $y = $m == 1 ? $y + 1 : $y;
    }
    
    error_log($log_prefix . 'SKY : ' . print_r($list_add_task, true));
    error_log(count($list_add_task));
    return $list_add_task;
}

function get_holiday_name($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $list_holiday_name = [];
    for ($j = 0; $j < 4; $j++) {
        $yyyy = date('Y', strtotime('+' . $j . ' years'));

        $url = 'http://calendar-service.net/cal?start_year=' . $yyyy
            . '&start_mon=1&end_year=' . $yyyy . '&end_mon=12'
            . '&year_style=normal&month_style=numeric&wday_style=ja_full&format=csv&holiday_only=1&zero_padding=1';

        $res = $mu_->get_contents($url, null, true);
        $res = mb_convert_encoding($res, 'UTF-8', 'EUC-JP');

        $tmp = explode("\n", $res);
        array_shift($tmp); // ヘッダ行削除
        array_pop($tmp); // フッタ行(空行)削除

        for ($i = 0; $i < count($tmp); $i++) {
            $tmp1 = explode(',', $tmp[$i]);
            $list_holiday_name[] = explode(',', $tmp[$i])[7];
        }
    }
    $list_holiday_name = array_unique($list_holiday_name);
    error_log($log_prefix . '$list_holiday_name : ' . print_r($list_holiday_name, true));

    return $list_holiday_name;
}