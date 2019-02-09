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

for ($i = 0; $i < 0; $i++) {
    $msg_no = $res[$i];

    $header = imap_header($imap, $msg_no);
    // error_log(print_r($header, true));

    $date = $header->Date;

    if (strpos($date, ' 2018 ') > 0) {
        $res = imap_mime_header_decode($header->Subject);
        error_log(print_r($res, true));
    }
}

imap_expunge($imap);
imap_close($imap);
$res = imap_errors();
error_log(print_r($res, true));
