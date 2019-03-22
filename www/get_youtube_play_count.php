<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$rc = get_youtube_play_count($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function get_youtube_play_count($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = getenv('URL_YOUTUBE');
    
    $res = $mu_->get_contents($url);
    
    $tmp = explode('window["ytInitialData"] = ', $res);
    $tmp = explode('window["ytInitialPlayerResponse"]', $tmp[1]);
    
    $playlist = [];
    $json = json_decode(trim(trim($tmp[0]), ';'));
    foreach ($json->contents->twoColumnWatchNextResults->playlist->playlist->contents as $item) {
        $title = $item->playlistPanelVideoRenderer->title->simpleText;
        $thumbnail = $item->playlistPanelVideoRenderer->thumbnail->thumbnails[0]->url;
        $url = $item->playlistPanelVideoRenderer->navigationEndpoint->commandMetadata->webCommandMetadata->url;
        $url = 'https://www.youtube.com' . $url;
        foreach (explode('&', parse_url($url, PHP_URL_QUERY)) as $param) {
            if (explode('=', $param)[0] = 'v') {
                $url = explode('?', $url)[0] . '?' . $param;
                break;
            }
        }
        $time = $item->playlistPanelVideoRenderer->lengthText->simpleText;
        $thumbnail = explode('?', $thumbnail)[0];
        $data['title'] = $title;
        $data['thumbnail'] = $thumbnail;
        $data['time'] = $time;
        $playlist[$url] = $data;
    }
    
    foreach (array_keys($playlist) as $url) {
        $res = $mu_->get_contents($url);
        $tmp = explode('window["ytInitialData"] = ', $res);
        $tmp = explode('window["ytInitialPlayerResponse"]', $tmp[1]);
        $json = json_decode(trim(trim($tmp[0]), ';'));
        $count = $json->contents->twoColumnWatchNextResults->results->results->contents[0]->videoPrimaryInfoRenderer->viewCount;
        $count = trim($count->videoViewCountRenderer->viewCount->simpleText);
        $count = explode(' ', $count)[0];
        $data = $playlist[$url];
        $data['count'] = $count;
        $playlist[$url] = $data;
    }
    
    error_log($log_prefix . print_r($playlist, true));
    
    $content = '';
    foreach (array_keys($playlist) as $url) {
        $data = $playlist[$url];
        $content .= $data['title'] . ' ' . $data['count'] . "\n";
    }
    error_log($log_prefix . $content);
    $mu_->post_blog_livedoor('Play Count', $content);
}
