<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

/*
$url = 'https://github.com/apache/httpd/releases.atom';
$res = $mu->get_contents($url);

$doc = new DOMDocument();
$doc->loadXML($res);

$xpath = new DOMXpath($doc);
$xpath->registerNamespace('ns', 'http://www.w3.org/2005/Atom');

$elements = $xpath->query("//ns:entry/ns:title");

$list_version = [];
foreach ($elements as $element) {
    $tmp = $element->nodeValue;
    $tmp = explode('.', $tmp);
    $list_version[(int)$tmp[0] * 1000000 + (int)$tmp[1] * 1000 + (int)$tmp[2]] = $element->nodeValue;
}
krsort($list_version);
$version_latest = array_shift($list_version);
error_log($version_latest);

$res = file_get_contents('/tmp/apache_current_version');
$res = trim(str_replace(["\r\n", "\r", "\n", '   ', '  '], ' ', $res));
error_log($res);

$url = 'https://devcenter.heroku.com/articles/php-support';
$res = $mu->get_contents($url);

$rc = preg_match('/<strong><a href="http:\/\/httpd.apache.org">Apache<\/a>(.+?)<\/strong> \((.+?)\) and <strong>/s', $res, $match);
// error_log(print_r($match, true));
error_log($match[2]);
*/
check_version_apache($mu);

$time_finish = microtime(true);
// $mu->post_blog_wordpress($requesturi . ' ' . substr(($time_finish - $time_start), 0, 6) . 's');
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

exit();

function check_version_apache($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://github.com/apache/httpd/releases.atom';
    $res = $mu_->get_contents($url);
    
    $doc = new DOMDocument();
    $doc->loadXML($res);
    
    $xpath = new DOMXpath($doc);
    $xpath->registerNamespace('ns', 'http://www.w3.org/2005/Atom');
    
    $elements = $xpath->query("//ns:entry/ns:title");
    
    $list_version = [];
    foreach ($elements as $element) {
        $tmp = $element->nodeValue;
        $tmp = explode('.', $tmp);
        $list_version[(int)$tmp[0] * 1000000 + (int)$tmp[1] * 1000 + (int)$tmp[2]] = $element->nodeValue;
    }
    krsort($list_version);
    $version_latest = array_shift($list_version);
    
    $res = file_get_contents('/tmp/apache_current_version');
    $version_current = trim(str_replace(["\r\n", "\r", "\n", '   ', '  '], ' ', $res));
    
    $url = 'https://devcenter.heroku.com/articles/php-support';
    $res = $mu_->get_contents($url);

    $rc = preg_match('/<strong><a href="http:\/\/httpd.apache.org">Apache<\/a>(.+?)<\/strong> \((.+?)\) and <strong>/s', $res, $match);
    $version_support = $match[2];

    error_log($log_prefix . '$version_latest : ' . $version_latest);
    error_log($log_prefix . '$version_support : ' . $version_support);
    error_log($log_prefix . '$version_current : ' . $version_current);
    
    $mu_->post_blog_wordpress('Apache Version', "latest : ${version_latest}\nsupport : ${version_support}\ncurrent : ${version_current}");
}

