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

ini_set('max_execution_time', 3600);

$mu = new MyUtils();

$url = 'https://' . parse_url(getenv('TEST_URL_010'))['host'];

$options1 = [
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'DNT: 1',
        'Upgrade-Insecure-Requests: 1',
        ],
];

// $res = $mu->get_contents($url, $options1);


for ($number = 663; $number < 707; $number++) {
    
    $url = str_replace('__NUMBER__', $number, getenv('TEST_URL_020')) . '1&per=150';
    $res = $mu->get_contents($url, $options1);
    
    error_log($res);
    break;
    $rc = preg_match_all('/page=(\d+)"/s', $res, $matches);
    
    $list_page = array_unique($matches[1]);
    rsort($list_page, SORT_NUMERIC);
    
    error_log(print_r($list_page, true));
    
    if (count($list_page) > 0) {
        $loop_end = $list_page[0];
    } else {
        $loop_end = 1;
    }
    
    for ($i = 0; $i < $loop_end; $i++) {
        $continue_flag = false;
        $url = str_replace('__NUMBER__', $number, getenv('TEST_URL_020')) . ($i + 1);

        if ($i > 0) {
            $res = $mu->get_contents($url, $options1);
        }

        $res = explode('<div class="pager">', $res)[1];
        $items = explode('<div class="rentalable">', $res);

        $urls = [];
        $results = [];
        foreach ($items as $item) {
            $rc = preg_match('/<a class=".+?type_free.+?data-remote="true" href="(.+?)"/s', $item, $match);
            if ($rc != 1) {
                continue;
            }

            $url = 'https://' . parse_url(getenv('TEST_URL_010'))['host'] . $match[1];
            $urls[$url] = $options1;
            $continue_flag = true;
        }
        if (count($urls) > 0) {
            $results = $mu->get_contents_multi($urls, null);
        }
        $urls = [];
        if (count($results) > 0) {
            foreach ($results as $result) {
                // error_log($result);
                $rc = preg_match('/<a id=".+?type_free.+?href="(.+?)".*?>(.+?)<.+?<p class="coinRight2_blue">(.+?)</s', $result, $match);
                $coin_own = (int)$match[3];
                $coin_need = (int)trim($match[2]);
                $url = 'https://' . parse_url(getenv('TEST_URL_010'))['host'] . $match[1];
                error_log("own : ${coin_own} / need : ${coin_need}");
                if ($coin_own == 0) {
                    // continue;
                    break 3;
                }
                $urls[$url] = $options1;
            }
            if (count($urls) > 0) {
                $mu->get_contents_multi($urls, null);
            }
        }
    }
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
