<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190415($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190415($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $hatena_id = $mu_->get_env('HATENA_ID', true);
    $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
    $hatena_api_key = $mu_->get_env('HATENA_API_KEY', true);
    
    $xml = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app">
  <title>__TITLE__</title>
  <content type="text/plain">__CONTENT__</content>
</entry>
__HEREDOC__;
    
    $xml = str_replace('__TITLE__', date('Y/m/d H:i:s', strtotime('+9 hours')) . ' TEST', $xml);
    $xml = str_replace('__CONTENT__', htmlspecialchars(nl2br('TEST')), $xml);
    
    error_log($logprefix . strlen($xml));
    error_log($logprefix . strlen(gzencode($xml, 9)));
    $xml_compress = gzencode($xml, 9);
    
    $url = "https://blog.hatena.ne.jp/${hatena_id}/${hatena_blog_id}/atom/entry";
    
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${hatena_id}:${hatena_api_key}",
        CURLOPT_POST => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => ['Expect:',
                               'Content-Encoding: gzip',
                               'Content-Length: ' . strlen($xml_compress),
                              ],
        CURLOPT_POSTFIELDS => $xml_compress,
    ];
    
    $res = $mu_->get_contents($url, $options);
    
    error_log($log_prefix . 'RESULT : ' . $res);
}
