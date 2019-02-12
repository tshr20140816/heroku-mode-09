<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

check_php_version($mu, '/tmp/dummy');

function check_php_version($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
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
    // error_log($version_latest);
    
    $res = file_get_contents('/tmp/php_current_version');
    $version_current = trim(str_replace(["\r\n", "\r", "\n", '   ', '  '], ' ', $res));

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
    // error_log($version_support);
    
    error_log($log_prefix . '$version_latest : ' . $version_latest);
    error_log($log_prefix . '$version_support : ' . $version_support);
    error_log($log_prefix . '$version_current : ' . $version_current);
    
    $content = "\nPHP Version\nlatest : ${version_latest}\nsupport : ${version_support}\ncurrent : ${version_current}\n";
    file_put_contents($file_name_blog_, $content, FILE_APPEND);
}
