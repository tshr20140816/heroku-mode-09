<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$html = <<< __HEREDOC__
<html><body>
<form method="POST" action="./imap.php">
<input type="text" name="user" />
<input type="password" name="password" />
<input type="text" name="message_number" />
<input type="submit" /> 
</form>
</body></html>
__HEREDOC__;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['user'];
    $password = $_POST['password'];
    $message_number = $_POST['message_number'];
    
    $imap = imap_open('{imap.mail.yahoo.co.jp:993/ssl}', $user, $password);
    
    $header = imap_header($imap, $message_number);
    $body = imap_fetchbody($imap, $message_number, 1);
    
    error_log('header : ' . print_r($header, true));
    error_log('body : ' . $body);
    error_log('body quoted_printable_decode : ' . quoted_printable_decode($body));
    
    imap_close($imap);
} else {
    echo $html;
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
