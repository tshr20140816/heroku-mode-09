<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$ueragent = $_SERVER['HTTP_USER_AGENT'];
error_log("${pid} USER AGENT : ${ueragent}");

$mu = new MyUtils();

header('Content-Type: application/xml');

$pdo = $mu->get_pdo();

$sql = 'SELECT T1.rss_data FROM t_rss T1 WHERE T1.rss_id = 1';
$rss_data = '';
foreach ($pdo->query($sql) as $row) {
    $rss_data = $row['rss_data'];
    break;
}

$pdo = null;

if ($rss_data != '') {
    echo gzdecode(base64_decode($rss_data));
}

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

exit();
