<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

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
    CURLOPT_TIMEOUT => 20,
];

$file_name = '/tmp/list_number';

$list_number = [];
if (file_exists($file_name)) {
    $list_number = unserialize(file_get_contents($file_name));
}
error_log(print_r($list_number, true));
for ($j = 1; $j < 1500; $j++) {
    if ((int)date('i') < 8) {
        break;
    }
    if (in_array($j, $list_number)) {
        continue;
    }
    
    $url = str_replace('__NUMBER__', $j, getenv('TEST_URL_020')) . '1';
    $res = $mu->get_contents($url, $options1);

    $rc = preg_match('/ type_free "/s', $res, $match);
    if (strpos($res, ' type_free ') === false) {
        continue;
    }
    $list_number[] = $j;
    
    file_put_contents($file_name, serialize($list_number));
    error_log('HIT ${j}');
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
