<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$rc = func_test($mu, '/tmp/dummy');

function func_test($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://devcenter.heroku.com/articles/ruby-support?' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);
    
    $tmp = explode('<p><strong>MRI:</strong></p>', $res);
    $tmp = explode('</ul>', $tmp[1]);
    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $tmp[0], $matches);

    rsort($matches[1]);
    $version_support = '';
    foreach ($matches[1] as $line) {
        $version_support .= trim(strip_tags($line)) . "\n";
    }
    
    $url = getenv('TARGET_GEM_FILE') . '?' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);
    $rc = preg_match('/ruby "(.+?)"/', $res, $match);
    $version_current = $match[1];
    
    $content = "\nRuby Version\ncurrent : ${version_current}\nsupport : ${version_support}";
    error_log($content);
    file_put_contents($file_name_blog_, $content, FILE_APPEND);
}
