<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$rc = func_test3($mu, '/tmp/dummy');
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test3($mu_, $file_name_blog_)
{
    $url = 'http://blog.livedoor.jp/tshr20140816/search?q=Play+Count';
    
    $res = $mu_->get_contents($url);
    // error_log($res);
    $rc = preg_match('/<div class="article-body-inner">(.+?)<\/div>/s', $res, $match);
    
    $tmp = explode('<br />', trim($match));
    error_log(print_r($tmp, true));
}
