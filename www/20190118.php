<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

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

$time_finish = microtime(true);
// $mu->post_blog_wordpress($requesturi . ' ' . substr(($time_finish - $time_start), 0, 6) . 's');
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

exit();
