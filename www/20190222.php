<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// Access Token
$access_token = $mu->get_access_token();

func_test($mu, '/tmp/dummy');

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test($mu_, $file_name_blog_)
{
    $xml = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app">
  <title>__TITLE__</title>
  <content type="text/plain">__CONTENT__</content>
</entry>
__HEREDOC__;
    
    $xml = str_replace('__TITLE__', '日本語テスト', $xml);
    $xml = str_replace('__CONTENT__', '日本語', $xml);
    
    $hatena_id = getenv('HATENA_ID');
    $hatena_blog_id = getenv('HATENA_BLOG_ID');
    $hatena_api_key = getenv('HATENA_API_KEY');

    $url = "https://blog.hatena.ne.jp/${hatena_id}/${hatena_blog_id}/atom/entry";
    
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${hatena_id}:${hatena_api_key}",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HEADER => true,
    ];
    
    $rc = $mu_->get_contents($url, $options);
}
