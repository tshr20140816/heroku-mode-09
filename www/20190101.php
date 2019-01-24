<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

if (!isset($_GET['n'])
    || $_GET['n'] === ''
    || is_array($_GET['n'])
    || !ctype_digit($_GET['n'])
   ) {
    error_log("${pid} FINISH Invalid Param");
    exit();
}

$n = (int)$_GET['n'];

$mu = new MyUtils();

$options1 = [
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, text/javascript, */*; q=0.01',
        'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'DNT: 1',
        'Upgrade-Insecure-Requests: 1',
        ],
];

for ($j = $n; $j < 1500; $j++) {
    if ((int)date('i') < 8) {
        break;
    }
    if ($j > 9 && $j < 534) {
        $j = 534;
    }
    
    $sum_point = 0;
    
    $url = str_replace('__NUMBER__', $j, getenv('TEST_URL_020')) . '1&per=150';
    $res = $mu->get_contents($url, $options1);
    
    $rc = preg_match_all('/page=(\d+)"/s', $res, $matches);
    
    $list_page = array_unique($matches[1]);
    rsort($list_page, SORT_NUMERIC);
    
    error_log(print_r($list_page, true));
    
    if (count($list_page) == 0) {
        $loop_end = 1;
    } else {
        $loop_end = $list_page[0];
    }
    
    // error_log($res);
    
    for ($i = 0; $i < $loop_end; $i++) {
        $continue_flag = false;
        $url = str_replace('__NUMBER__', $j, getenv('TEST_URL_020')) . ($i + 1) . '&per=150';

        if ($i > 0) {
            $res = $mu->get_contents($url, $options1);
        }

        $res = explode('<div class="pager">', $res)[1];
        $items = explode('<div class="rentalable">', $res);

        foreach ($items as $item) {
            $rc = preg_match('/<a class=".+?type_free.+? href=".+?".*?>(.+?)<\/a>/s', $item, $match);
            if ($rc != 1) {
                continue;
            }
            // error_log(print_r($match, true));
            $point = (int)$match[1];
            $sum_point += $point;
        }
    }
    if ($sum_point > 0) {
        error_log("__TOTAL_POINT__ : ${sum_point} ${j}");
    }
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
