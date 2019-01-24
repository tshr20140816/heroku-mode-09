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

// error_log($res);

$rc = preg_match_all('/<a href=".*?\/series\/(\d+)"/s', $res, $matches);

$list_number = array_unique($matches[1]);
sort($list_number, SORT_NUMERIC);

for ($i = 0; $i < count($list_number); $i++) {
    if ($list_number[$i] == $n) {
        $list_number = array_slice($list_number, $i);
        break;
    }
}

error_log(print_r($list_number, true));

// exit();

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

// error_log($res);

// exit();

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

$options4 = [
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, text/javascript, */*; q=0.01',
        'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'DNT: 1',
        'Upgrade-Insecure-Requests: 1',
        ],
    CURLOPT_NOBODY => true,
];

$options5 = [
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
    CURLOPT_NOBODY => true,
];

foreach ($list_number as $number) {
    if ((int)date('i') < 8) {
        break;
    }
    
    $url = str_replace('__NUMBER__', $number, getenv('TEST_URL_020')) . '1';
    $res = $mu->get_contents($url, $options1);
    
    $rc = preg_match_all('/page=(\d+)"/s', $res, $matches);
    
    $list_page = array_unique($matches[1]);
    rsort($list_page, SORT_NUMERIC);
    
    error_log(print_r($list_page, true));
    
    $loop_end = $list_page[0] + 1;
    
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
        /*
        if ($continue_flag === false) {
            break;
        }
        */
    }
}

$url = 'https://' . parse_url(getenv('TEST_URL_010'))['host'] . '/api/v1/me/coin';
$res = $mu->get_contents($url, $options3);
error_log($res);

error_log(file_get_contents($cookie));

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
