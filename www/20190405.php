<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$sub_address = $mu->get_env('SUB_ADDRESS');
for ($i = 11; $i > -1; $i--) {
    $url = 'https://feed43.com/' . $sub_address . ($i * 5 + 11) . '-' . ($i * 5 + 15) . '.xml';
    $urls[$url] = null;
}

$multi_options = [
    CURLMOPT_PIPELINING => 3,
    CURLMOPT_MAX_HOST_CONNECTIONS = 2,
];

// multi
$list_contents = $mu->get_contents_multi($urls, null, $multi_options);

// error_log(print_r($list_contents, true));

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

exit();
