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

$cookie = $tmpfname = tempnam("/tmp", time());

$url = getenv('TEST_URL_030');

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
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
    CURLOPT_TIMEOUT => 20,
];

$res = $mu->get_contents($url, $options1);

$rc = preg_match_all('/<a href=".*?\/series\/(\d+)"/s', $res, $matches);

$list_number = array_unique($matches[1]);
sort($list_number, SORT_NUMERIC);

$res = file_get_contents('https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/data.txt');
$list_number = array_merge($list_number, explode(',', trim($res)));

$list_number = array_unique($list_number);
array_unshift($list_number, 679);
array_unshift($list_number, 562);
array_unshift($list_number, 862);
array_unshift($list_number, 549);
array_unshift($list_number, 1180);

for ($i = 0; $i < count($list_number); $i++) {
    if ($list_number[$i] == $n) {
        $list_number = array_slice($list_number, $i);
        break;
    }
}

error_log(print_r($list_number, true));

$url = getenv('TEST_URL_010');

$res = $mu->get_contents($url, $options1);

$rc = preg_match('/<input.+?name="utf8".+?value="(.*?)".+?<input.+?name="authenticity_token".+?value="(.*?)"/s', $res, $match);
// error_log(print_r($match, true));
$utf8 = $match[1];
$authenticity_token = $match[2];

$post_data = [
    'utf8' => $utf8,
    'authenticity_token' => $authenticity_token,
    'account[email]' => getenv('TEST_ID'),
    'account[password]' => getenv('TEST_PASSWORD'),
];

$options2 = [
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'DNT: 1',
        'Upgrade-Insecure-Requests: 1',
        ],
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post_data),
];

$res = $mu->get_contents($url, $options2);

$options3 = [
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, text/javascript, */*; q=0.01',
        'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'DNT: 1',
        'Upgrade-Insecure-Requests: 1',
        ],
    CURLOPT_COOKIEJAR => $cookie,
    CURLOPT_COOKIEFILE => $cookie,
];

$urls2 = [];
$point_sum = 0;
foreach ($list_number as $number) {
    if ((int)date('i') < 8 && (int)date('i') > 4) {
        error_log('STOP TIME');
        break;
    }

    $url = str_replace('__NUMBER__', $number, getenv('TEST_URL_020')) . '1&per=150';
    $res = $mu->get_contents($url, $options1);

    $rc = preg_match_all('/page=(\d+).*?"/s', $res, $matches);

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
        $url = str_replace('__NUMBER__', $number, getenv('TEST_URL_020')) . ($i + 1) . '&per=150';

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
                $urls2[$url] = $options1;
                $point_sum += $coin_need;
            }
        }
        if (count($urls2) > 100) {
            error_log("own : ${coin_own}" . ' / count : ' . count($urls2) . " / point_sum : ${point_sum} / number : ${number}");
            $mu->get_contents_multi($urls2, null);
            $urls2 = [];
            $point_sum = 0;
        }
    }
}
if (count($urls2) > 0) {
    error_log("own : ${coin_own}" . ' / count : ' . count($urls2) . " / point_sum : ${point_sum} / number : ${number}");
    $mu->get_contents_multi($urls2, null);
}

error_log(file_get_contents($cookie));

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
