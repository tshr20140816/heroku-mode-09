<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$cookie = tempnam("/tmp", time());

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

$url = 'https://theoldreader.com/users/sign_in';

$res = $mu->get_contents($url, $options);

// error_log($res);

$rc = preg_match('/"authenticity_token".+?value="(.+?)"/', $res, $match);

error_log(print_r($match, true));

$post_data = ['authenticity_token' => $match[1],
             'utf8' => '&#x2713;',
             'user[login]' => getenv('TEST3_USER'),
             'user[password]' => getenv('TEST3_PASSWORD'),
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

error_log($res);

unlink($cookie);
