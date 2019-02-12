<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

check_php_version($mu);

function check_php_version($mu_) {
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'https://devcenter.heroku.com/articles/php-support?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);
    
    $rc = preg_match('/<h4 id="supported-versions-php">PHP<\/h4>.*?<ul>(.+?)<\/ul>/s', $res, $match);
    // error_log(print_r($match, true));
    
    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $match[1], $matches);
    // error_log(print_r($matches, true));
    
    $list_version = [];
    foreach ($matches[1] as $item) {
        // error_log($item);
        $tmp = explode('.', $item);
        $list_version[$tmp[0] * 10000 + $tmp[1] * 100 + $tmp[2]] = $item;
    }
    krsort($list_version);
    // error_log(print_r($list_version, true));
    
    $version_support = array_shift($list_version);
    error_log($version_support);
    
    $url = 'https://github.com/php/php-src/releases.atom?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);
    
    $doc = new DOMDocument();
    $doc->loadXML($res);
    
    $xpath = new DOMXpath($doc);
    $xpath->registerNamespace('ns', 'http://www.w3.org/2005/Atom');
    
    $elements = $xpath->query("//ns:entry/ns:title");
    
    $list_version = [];
    foreach ($elements as $element) {
        $tmp = $element->nodeValue;
        if (strpos($tmp, 'RC') > 0) {
            continue;
        }
        $tmp = str_replace('php-', '', $tmp);
        $tmp = explode('.', $tmp);
        $list_version[(int)$tmp[0] * 10000 + (int)$tmp[1] * 100 + (int)$tmp[2]] = $element->nodeValue;
    }
    krsort($list_version);
    error_log(print_r($list_version, true));
    $version_latest = array_shift($list_version);
}
