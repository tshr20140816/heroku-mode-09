<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'https://github.com/apache/httpd/releases.atom';
$res = $mu->get_contents($url);

error_log($res);

$doc = new DOMDocument();
$doc->loadXML($res);

$xpath = new DOMXpath($doc);
$elements = $xpath->query("*/entry/title");

foreach ($elements as $element) {
    error_log($element->nodeName);
}


$time_finish = microtime(true);
// $mu->post_blog_wordpress($requesturi . ' ' . substr(($time_finish - $time_start), 0, 6) . 's');
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

exit();
