<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$cookie = $tmpfname = tempnam("/tmp", time());

$url = getenv('TEST_URL_010');

$options = [
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
];

$res = $mu->get_contents($url, $options);

// error_log($res);

$rc = preg_match('/<input.+?name="utf8".+?value="(.*?)"/s', $res, $match);
error_log(print_r($match, true));
$utf8 = $match[1];

$rc = preg_match('/<input.+?name="authenticity_token".+?value="(.*?)"/s', $res, $match);
error_log(print_r($match, true));
$authenticity_token = $match[1];

$post_data = [
    'utf8' => $utf8,
    'authenticity_token' => $authenticity_token,
    'account[email]' => getenv('TEST_ID'),
    'account[password]' => getenv('TEST_PASSWORD'),    
];

$options = [
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

$res = $mu->get_contents($url, $options);

// error_log($res);

// $url = 'https://' . parse_url($url)['host'];
$url = getenv('TEST_URL_020');

$options = [
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
];

$res = $mu->get_contents($url, $options);

// error_log($res);

$res = explode('<main>', $res)[1];

$rc = preg_match_all('/<a class=".+?type_free.+?data-remote="true" href="(.+?)"/s', $res, $matches, PREG_SET_ORDER );
error_log(print_r($matches, true));

error_log(file_get_contents($cookie));

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ');
