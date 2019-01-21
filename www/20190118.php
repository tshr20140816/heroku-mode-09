<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

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
    
    $y = date('Y');
    for ($i = 0; $i < 3; $i++) {
        $url = 'https://e-moon.net/calendar_list/calendar_moon_' . ($y + $i) . '/';
        if ($i > 0) {
            $res = $mu_->get_contents($url, [CURLOPT_NOBODY => true]);
            if ($res != '') {
                continue;
            }
        }
        $res = $mu_->get_contents($url);
        
        $pattern = '/<td class="embed_link_to_star_mall_fullmoon">(\d+).+?(\d+).+?(\d+).+?(\d+)(.*?)</s';
        $rc = preg_match_all($pattern, $res, $matches,  PREG_SET_ORDER);
        
        foreach($matches as $match) {
            for ($j = 1; $j < 5; $j++) {
                $match[$j] = str_pad($match[$j], 2, '0', STR_PAD_LEFT);
            }
            $match[5] = trim($match[5]);
            if ($match[5] == '') {
                $match[5] = '満月';
            }
            $title = $match[1] . '/' . $match[2] . ' ' . $match[3] . ':' . $match[4] . ' ' .  $match[5] . ' ★';
            $duedate = mktime(0, 0, 0, $match[1], $match[2], $y + $i);
        }
    }
    
    error_log($log_prefix . 'FULL MOON : ' . print_r($list_add_task, true));
    return $list_add_task;
}

function check_version_apache($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://e-moon.net/calendar_list/calendar_moon_2019/';
    $res = $mu_->get_contents($url);
    
    // error_log($res);
    
    $rc = preg_match_all('/<td class="embed_link_to_star_mall_fullmoon">(\d+).+?(\d+).+?(\d+).+?(\d+)(.*?)</s', $res, $matches,  PREG_SET_ORDER);
    
    // error_log(print_r($matches, true));
    
    $y = 2019;
    foreach($matches as $match) {
        for ($i = 1; $i < 5; $i++) {
            $match[$i] = str_pad($match[$i], 2, '0', STR_PAD_LEFT);
        }
        $match[5] = trim($match[5]);
        if ($match[5] == '') {
            $match[5] = '満月';
        }
        $list[] = $match[1] . '/' . $match[2] . ' ' . $match[3] . ':' . $match[4] . ' ' .  $match[5] . ' ★';
    }
    error_log(print_r($list, true));
    
    $url = 'https://www.nao.ac.jp/astro/sky/2019/01.html';
    $res = $mu_->get_contents($url);
    
    // error_log($res);
    
    $rc = preg_match_all('/<tr>.+?<td.*?>(\d+).+?<\/td>.*?<td.*?>(.+?)<\/td>.*?<\/tr>/s', $res, $matches,  PREG_SET_ORDER);
    
    // error_log(print_r($matches, true));
    
    $y = 2019;
    $m = 1;
    foreach($matches as $match) {
        $list[] = str_pad($m, 2, '0', STR_PAD_LEFT) . '/' . str_pad($match[1], 2, '0', STR_PAD_LEFT) . ' ' . mb_convert_kana(strip_tags($match[2]), 'a') . ' ★';
        /*
        array_shift($match);
        $match[0] = trim($match[0], '日');
        $match[1] = mb_convert_kana(strip_tags($match[1]), 'a');
        error_log(print_r($match, true));
        */
    }
    error_log(print_r($list, true));
    
    $url = 'https://www.nao.ac.jp/astro/sky/2020/01.html';
    $res = $mu_->get_contents($url, [CURLOPT_NOBODY => true]);
    
    error_log($res);
    
    $url = 'https://e-moon.net/calendar_list/calendar_moon_2021/';
    $res = $mu_->get_contents($url, [CURLOPT_NOBODY => true]);
    
    error_log($res);
}
