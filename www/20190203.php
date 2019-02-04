<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$cookie = $tmpfname = tempnam("/tmp", time());

$url = 'https://www.toodledo.com/signin.php?redirect=/tools/backup.php';

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

$rc = preg_match('/<input .+? name="csrf1" value="(.*?)"/s', $res, $matches);
$csrf1 = $matches[1];
$rc = preg_match('/<input .+? name="csrf2" value="(.*?)"/s', $res, $matches);
$csrf2 = $matches[1];

$post_data = [
    'csrf1' => $csrf1,
    'csrf2' => $csrf2,
    'redirect' => /tools/backup.php,
    'email' => getenv('TOODLEDO_EMAIL'),
    'password' => getenv('TOODLEDO_PASSWORD'),
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
    CURLOPT_TIMEOUT => 20,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post_data),
];

$url = 'https://www.toodledo.com/signin.php';

$res = $mu->get_contents($url, $options2);

error_log(strlen($res));
