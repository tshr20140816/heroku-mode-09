<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// 

$imap = imap_open('{imap.mail.yahoo.co.jp:993/ssl}', getenv('TEST2_ID'), getenv('TEST2_PASSWORD'));

// error_log($mbox);
error_log(print_r($imap, true));

$res = imap_ping($imap);
error_log('imap_ping : ' . print_r($res, true));

$res = imap_check($imap);
error_log('imap_check : ' . print_r($res, true));

$res = imap_search($imap, 'ALL');

// error_log(print_r($res, true));
error_log('COUNT : ' . count($res));

if (count($res) == 0) {
    exit();
}

$header = imap_header($imap, $msg_no);
$body = imap_body($imap, $msg_no);

error_log(print_r($header, true));
error_log(print_r($body, true));

imap_close($imap);
