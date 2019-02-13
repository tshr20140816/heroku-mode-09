<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();


// https://inoreader.superfeedr.com/

check_version_curl($mu, '/tmp/dummy');

function check_version_curl($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://github.com/curl/curl/releases.atom?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);
    
    $doc = new DOMDocument();
    $doc->loadXML($res);
    
    $xpath = new DOMXpath($doc);
    $xpath->registerNamespace('ns', 'http://www.w3.org/2005/Atom');
    
    $elements = $xpath->query("//ns:entry/ns:title");
    
    $version_latest = $elements[0]->nodeValue;
    
    $res = file_get_contents('/tmp/curl_current_version');
    $version_current = trim(str_replace(["\r\n", "\r", "\n", '   ', '  '], ' ', $res));
    
    error_log($log_prefix . '$version_latest : ' . $version_latest);
    error_log($log_prefix . '$version_current : ' . $version_current);
    
    $content = "\ncurl Version\nlatest : ${version_latest}\ncurrent : ${version_current}\n";
    file_put_contents($file_name_blog_, $content, FILE_APPEND);
}
