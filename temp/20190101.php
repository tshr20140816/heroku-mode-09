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

$list_number = array_unique($matches[1]);
sort($list_number, SORT_NUMERIC);

$res = file_get_contents('https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/data.txt');
$list_number = array_merge($list_number, explode(',', $res));

$list_number = array_unique($list_number);

for ($i = 0; $i < count($list_number); $i++) {
    if ($list_number[$i] == $n) {
        $list_number = array_slice($list_number, $i);
        break;
    }
}

error_log(print_r($list_number, true));

$coin_own_current = 0;

foreach ($list_number as $number) {
    /*
    if ((int)date('i') < 8) {
        error_log('STOP TIME');
        break;
    }
    */
    
    $url = str_replace('__NUMBER__', $number, getenv('TEST_URL_020')) . '1';
    $res = $mu->get_contents($url, $options1);
    
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

        $url = str_replace('__NUMBER__', $number, getenv('TEST_URL_020')) . ($i + 1);
        if ($i > 0) {
            $res = $mu->get_contents($url, $options1);
        }

        $res = explode('<div class="pager">', $res)[1];
        $items = explode('<div class="rentalable">', $res);

        foreach ($items as $item) {
            $rc = preg_match('/<a class=".+?type_free.+?data-remote="true" href="(.+?)"/s', $item, $match);
            if ($rc != 1) {
                continue;
            }

            $url = 'https://' . parse_url(getenv('TEST_URL_010'))['host'] . $match[1];
            
            $res = $mu->get_contents($url, $options1);
            
            $rc = preg_match('/<a id=".+?type_free.+?href="(.+?)".*?>(.+?)<.+?<p class="coinRight2_blue">(.+?)</s', $res, $match);
            if ($rc != 1) {
                continue;
            }
            
            $coin_own = (int)$match[3];
            $coin_need = (int)trim($match[2]);
            error_log("own : ${coin_own} / need : ${coin_need}");
            if ($coin_own_current === 0) {
                $coin_own_current = $coin_own;
            }
            if ($coin_own_current != $coin_own) {
                error_log('UP LIMIT');
                break 3;                
            }
            $url = 'https://' . parse_url(getenv('TEST_URL_010'))['host'] . $match[1];
            $res = $mu->get_contents($url, $options1);
            
            $rc = get_point($mu, $cookie);
            error_log(print_r($rc, true));
            if ($rc[1] < 30) {
                error_log('POINT LIMIT');
                break 3;
            }            
        }
    }
}
error_log(file_get_contents($cookie));

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');

exit();

function get_point($mu_, $cookie_) {
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
        CURLOPT_COOKIEJAR => $cookie_,
        CURLOPT_COOKIEFILE => $cookie_,
    ];

    $url = 'https://' . parse_url(getenv('TEST_URL_010'))['host'] . '/api/v1/me/coin';
    $res = $mu_->get_contents($url, $options3);
    $res = json_decode($res);
    // error_log(print_r($res, true));
    $percentage_complete_level_up = $res->data->percentage_complete_level_up;
    $total_coin = $res->data->total_coin;
    
    return [$percentage_complete_level_up, $total_coin];
}