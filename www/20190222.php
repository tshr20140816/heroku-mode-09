<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// Access Token
$access_token = $mu->get_access_token();

func_test($mu, 'TEST', 'TEST');

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test($mu_, $title_, $description_ = null)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $xml = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app">
  <title>__TITLE__</title>
  <content type="text/plain">__CONTENT__</content>
</entry>
__HEREDOC__;

    $xml = str_replace('__TITLE__', $title_, $xml);
    $xml = str_replace('__CONTENT__', $description_, $xml);

    $hatena_id = base64_decode(getenv('HATENA_ID'));
    $hatena_blog_id = base64_decode(getenv('HATENA_BLOG_ID'));
    $hatena_api_key = base64_decode(getenv('HATENA_API_KEY'));

    $url = "https://blog.hatena.ne.jp/${hatena_id}/${hatena_blog_id}/atom/entry";

    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${hatena_id}:${hatena_api_key}",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HEADER => true,
    ];

    $res = $mu_->get_contents($url, $options);

    error_log($log_prefix . 'RESULT : ' . print_r($res, true));
}
