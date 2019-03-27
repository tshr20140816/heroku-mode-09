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
    
    $dic_item = [];
    foreach (explode('<br />', str_replace("\n", '', trim($match[1]))) as $item) {
        if (strlen($item) == 0) {
            continue;
        }
        $tmp = strrev($item);
        $tmp = explode(' ', $tmp, 2);
        $dic_item[strrev($tmp[1])] = strrev($tmp[0]);
    }
    error_log(print_r($dic_item, true));
}
