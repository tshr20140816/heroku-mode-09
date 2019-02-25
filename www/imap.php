<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

set_time_limit(60);

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

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $user = $_POST['user'];
    $password = $_POST['password'];
    $message_number = $_POST['message_number'];

    $imap = imap_open('{imap.mail.yahoo.co.jp:993/ssl}', $user, $password);

    $header = imap_header($imap, $message_number);
    $struct = imap_fetchstructure($imap, $message_number);

    error_log('header : ' . print_r($header, true));
    error_log('struct : ' . print_r($struct, true));

    if (isset($struct->parts)) {
        $loop_end = count($struct->parts);
    } else {
        $loop_end = 1;
    }

    for ($i = 0; $i < $loop_end; $i++) {
        if (isset($struct->parts)) {
            $charset = $struct->parts[$i]->parameters[0]->value;
            $encoding = $struct->parts[$i]->encoding;
        } else {
            $charset = $struct->parameters[0]->value;
            $encoding = $struct->encoding;
        }

        $body = imap_fetchbody($imap, $message_number, $i + 1);

        switch ($encoding) {
            case 1: // 8bit
                $body = imap_8bit($body);
                $body = imap_qprint($body);
                break;
            case 3: // Base64
                $body = imap_base64($body);
                break;
            case 4: // Quoted-Printable
                $body = imap_qprint($body);
                break;
            default:
                break;
        }
        $body = mb_convert_encoding($body, 'UTF-8', $charset);

        error_log('----- No. ' . ($i + 1) . ' -----');
        error_log($body);
    }

    imap_close($imap);
} else {
    echo $html;
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
