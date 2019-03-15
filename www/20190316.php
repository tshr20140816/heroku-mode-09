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
    
    $url = 'https://www.youtube.com/watch?v=mg01S3eosZ0&list=UUPPC65lyLljwbhAkyEBIQDw';
    
    $res = $mu_->get_contents($url, $options);
    
    $tmp = explode('window["ytInitialData"] = ', $res);
    $tmp = explode('window["ytInitialPlayerResponse"]', $tmp[1]);
    error_log($tmp);
    
    $json = json_decode(trim(trim($tmp[0]), ';'));
    foreach ($json->contents->twoColumnWatchNextResults->playlist->playlist->contents as $item) {
        error_log(print_r($item, true));
        /*
        $title = $item->gridVideoRenderer->title->simpleText;
        $thumbnail = $item->gridVideoRenderer->thumbnail->thumbnails[0]->url;
        $link = $item->gridVideoRenderer->navigationEndpoint->commandMetadata->webCommandMetadata->url;
        $count = $item->gridVideoRenderer->viewCountText->simpleText;
        $time = $item->gridVideoRenderer->thumbnailOverlays[0]->thumbnailOverlayTimeStatusRenderer->text->simpleText;
        $thumbnail = explode('?', $thumbnail)[0];
        error_log($title);
        error_log($thumbnail);
        error_log($link);
        error_log($count);
        error_log($time);
        */
        break;
    }
}
