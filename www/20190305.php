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
    
    // error_log($res);
    
    $tmp = explode('<p><strong>MRI:</strong></p>', $res);
    $tmp = explode('</ul>', $tmp[1]);
    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $tmp[0], $matches);
    error_log(print_r($matches, true));
    foreach ($matches[1] as $line) {
        error_log(trim(strip_tags($line)));
    }
    
    $url = getenv('TARGET_GEM_FILE') . '?' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);
    $rc = preg_match('/^ruby "(.+?)"/s', $res, $match);
    error_log(print_r($match, true));
}
