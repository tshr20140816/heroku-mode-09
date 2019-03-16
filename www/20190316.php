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
    
    $playlist = [];
    $urls = [];
    $json = json_decode(trim(trim($tmp[0]), ';'));
    foreach ($json->contents->twoColumnWatchNextResults->playlist->playlist->contents as $item) {
        //error_log(print_r($item, true));

        $title = $item->playlistPanelVideoRenderer->title->simpleText;
        $thumbnail = $item->playlistPanelVideoRenderer->thumbnail->thumbnails[0]->url;
        $url = $item->playlistPanelVideoRenderer->navigationEndpoint->commandMetadata->webCommandMetadata->url;
        $url = 'https://www.youtube.com/' . $url;
        foreach (explode('&', parse_url($url, PHP_URL_QUERY)) as $param) {
            if (explode('=', $param)[0] = 'v') {
                error_log($param);
                $url = explode('?', $url)[0] . '?' . $param;
                break;
            }
        }
        $time = $item->playlistPanelVideoRenderer->lengthText->simpleText;
        $thumbnail = explode('?', $thumbnail)[0];
        error_log($title);
        error_log($thumbnail);
        error_log($url);
        //error_log($count);
        error_log($time);
        $data['title'] = $title;
        $data['thumbnail'] = $thumbnail;
        $data['time'] = $time;
        $playlist[$url] = $data;
        $urls[$url] = null;
        break;
    }
    
    $list_contents = $mu_->get_contents_multi($urls);
    
    foreach ($list_contents as $url => $content) {
        $tmp = explode('window["ytInitialData"] = ', $content);
        $tmp = explode('window["ytInitialPlayerResponse"]', $tmp[1]);
        $json = json_decode(trim(trim($tmp[0]), ';'));
        $count = $json->contents->twoColumnWatchNextResults->results->results->contents[0]->videoPrimaryInfoRenderer->viewCount;
        $count = $count->videoViewCountRenderer->viewCount->simpleText;
        error_log($count);
        $data = $playlist[$url];
        $data['count'] = $count;
        $playlist[$url] = $data;
    }
    error_log(print_r($playlist, true));
}
