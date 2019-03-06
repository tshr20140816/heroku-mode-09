<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

post_blog_livedoor('TEST ' . microtime(true), "ONE_LINE\nTWO_LINE");

function post_blog_livedoor($title_, $description_ = null)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    if (is_null($description_)) {
        $description_ = '.';
    }

    $livedoor_id = base64_decode(getenv('LIVEDOOR_ID'));
    $livedoor_atom_password = base64_decode(getenv('LIVEDOOR_ATOM_PASSWORD'));

    $xml = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app">
  <title>__TITLE__</title>
  <content type="text/plain">__CONTENT__</content>
</entry>
__HEREDOC__;

    $xml = str_replace('__TITLE__', date('Y/m/d H:i:s', strtotime('+9 hours')) . " ${title_}", $xml);
    $xml = str_replace('__CONTENT__', $description_, $xml);

    $url = "https://livedoor.blogcms.jp/atompub/${livedoor_id}/";

    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${livedoor_id}:${livedoor_atom_password}",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_HEADER => true,
    ];

    $res = $this->get_contents($url, $options);

    error_log($log_prefix . 'RESULT : ' . $res);
}
