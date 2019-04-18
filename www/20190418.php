<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190418($mu, '/tmp/dummy.txt');

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190418($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = getenv('TEST_URL_01');
    $host_name = parse_url($url, PHP_URL_HOST);
    $res = $mu_->get_contents($url, null, true);
    
    // error_log($res);
    $rc = preg_match_all('/<a class="title" href="(.+?)">/s', $res, $matches);
    // error_log(print_r($matches, true));
    $urls = [];
    foreach ($matches[1] as $item) {
        $url = 'https://' . $host_name . $item;
        // $urls[$url] = null;
        $res = $mu_->get_contents($url);
        // error_log($res);
        $rc = substr_count($res, '<item>');
        error_log($rc . ' ' . $url);
    }
    
    /*
    $multi_options = [
        CURLMOPT_PIPELINING => 3,
        CURLMOPT_MAX_HOST_CONNECTIONS => 1,
    ];
    for ($i = 0; $i < 10; $i++) {
        $list_contents = $mu_->get_contents_multi($urls, null, $multi_options);
        foreach ($list_contents as $url => $content) {
            if (strlen($content) < 10) {
                continue;
            }
            $rc = substr_count($content, '<item>');
            error_log($rc . ' ' .$url);
            unset($urls[$url]);
        }
        if (count($urls) === 0) {
            break;
        }
    }
    */
    
}
