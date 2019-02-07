<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// imap.mail.yahoo.co.jp 993 w36_5

$imap = imap_open('{imap.mail.yahoo.co.jp:993/ssl}w36_5', getenv('TEST2_ID'), getenv('TEST2_PASSWORD'));

// error_log($mbox);
error_log(print_r($imap, true));

$res = imap_search($imap, 'ALL');

// error_log(print_r($res, true));
error_log('COUNT : ' . count($res));

$header = imap_header($imap, $res[0]);

error_log(print_r($header, true));

$rc = imap_delete($imap, $res[0]);

error_log($rc);

$rc = imap_expunge($imap);

error_log($rc);

imap_close($imap);
