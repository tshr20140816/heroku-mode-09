<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// 

$imap = imap_open('{imap.mail.yahoo.co.jp:993/ssl}Page', getenv('TEST2_ID'), getenv('TEST2_PASSWORD'));

// error_log($mbox);
error_log(print_r($imap, true));

$res = imap_ping($imap);
error_log('imap_ping : ' . print_r($res, true));

$res = imap_check($imap);
error_log('imap_check : ' . print_r($res, true));

/*
$res = imap_get_quotaroot($imap, 'INBOX');
error_log('imap_get_quotaroot : ' . print_r($res, true));
*/

$res = imap_search($imap, 'ALL');

// error_log(print_r($res, true));
error_log('COUNT : ' . count($res));

if (count($res) == 0) {
    exit();
}

/*
$msg_no = $res[0];

$header = imap_header($imap, $msg_no);
error_log(print_r($header, true));

$res = imap_mime_header_decode($header->Subject);
error_log(print_r($res, true));

$res = imap_body($imap, $msg_no);
error_log($res);
*/

$loop_end = count($res);

for ($i = 0; $i < $loop_end; $i++) {
    $rc = imap_delete($imap, $res[$i]);
    // error_log($i . ' ' . $rc);
    if ($i % 100 == 0) {
        $rc = imap_expunge($imap);
        error_log($i . ' ' . $rc);
    }
}

$rc = imap_expunge($imap);
error_log($rc);

imap_close($imap);