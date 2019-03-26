<?php

$html = <<< __HEREDOC__
<html>
<head>
<meta http-equiv="refresh" content="600">
<title>__TITLE__</title>
</head>
<body>
<table>
__BODY__
</table>
</body></html>
__HEREDOC__;

error_log(print_r($_COOKIE));

$url = 'https://www.suzukacircuit.jp/f1/ticket/index.html';

$res = get_contents($url, [CURLOPT_HEADER => true, CURLOPT_NOBODY => true]);

error_log($res);
$rc = preg_match('/Last-Modified.+/', $res, $match);
// error_log(date('Ymd', strtotime(trim(explode(':', $match[0])[1]))));
$tmp = trim(explode(':', $match[0], 2)[1]);
error_log($tmp);
error_log(strtotime($tmp));
error_log(date('Y/m/d H:i:s', strtotime($tmp)));

$body = '<tr><td>' . $url . '</td><td>' . $match[0] . '</td></tr>' . "\n";

$html = str_replace('__BODY__', $body, $html);

$hash = hash('sha512', $html);
error_log($hash);

setcookie('hash', $hash, 0, '', '', true, true);

echo $html;

function get_contents($url_, $options_ = null)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    error_log($log_prefix . 'URL : ' . $url_);
    error_log($log_prefix . 'options : ' . print_r($options_, true));
    $options = [
        CURLOPT_URL => $url_,
        CURLOPT_USERAGENT => getenv('USER_AGENT'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_PATH_AS_IS => true,
        CURLOPT_TCP_FASTOPEN => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
    ];
    
    $ch = curl_init();
    foreach ($options as $key => $value) {
        $rc = curl_setopt($ch, $key, $value);
        if ($rc == false) {
            error_log($log_prefix . "curl_setopt : ${key} ${value}");
        }
    }
    if (is_null($options_) === false) {
        foreach ($options_ as $key => $value) {
            $rc = curl_setopt($ch, $key, $value);
            if ($rc == false) {
                error_log($log_prefix . "curl_setopt : ${key} ${value}");
            }
        }
    }
    $res = curl_exec($ch);
    $http_code = (string)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $res;
}
