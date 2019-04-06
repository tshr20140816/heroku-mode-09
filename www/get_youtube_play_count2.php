<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$rc = get_youtube_play_count2($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function get_youtube_play_count2($mu_)
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

    $multi_options = [
        CURLMOPT_PIPELINING => 3,
        CURLMOPT_MAX_HOST_CONNECTIONS => 10,
    ];
    for (;;) {
        $urls = [];
        foreach (array_keys($playlist) as $url) {
            if (array_key_exists('count', $playlist[$url])) {
                continue;
            }
            $urls[$url] = null;
            if (count($urls) === $multi_options[CURLMOPT_MAX_HOST_CONNECTIONS]) {
                break;
            }        
        }

        if (count($urls) === 0) {
            break;
        }

        $list_contents = $mu_->get_contents_multi($urls, null, $multi_options);
        foreach (array_keys($list_contents) as $url) {
            $tmp = explode('window["ytInitialData"] = ', $list_contents[$url]);
            $tmp = explode('window["ytInitialPlayerResponse"]', $tmp[1]);
            $json = json_decode(trim(trim($tmp[0]), ';'));
            $count = $json->contents->twoColumnWatchNextResults->results->results->contents[0]->videoPrimaryInfoRenderer;
            $count = trim($count->viewCount->videoViewCountRenderer->viewCount->simpleText);
            $count = explode(' ', $count)[0];
            $data = $playlist[$url];
            $data['count'] = $count;
            $playlist[$url] = $data;
        }
        error_log($log_prefix . 'memory_get_usage : ' . number_format(memory_get_usage()) . 'byte');
        $list_contents = [];
    }

    error_log($log_prefix . print_r($playlist, true));

    $livedoor_id = $mu_->get_env('LIVEDOOR_ID', true);
    $url = "http://blog.livedoor.jp/${livedoor_id}/search?q=Play+Count";
    $res = $mu_->get_contents($url);

    $rc = preg_match('/<div class="article-body-inner">(.+?)<\/div>/s', $res, $match);
    $dic_previous_count = [];
    foreach (explode('<br />', str_replace("\n", '', trim($match[1]))) as $item) {
        if (strlen($item) == 0) {
            continue;
        }
        $tmp = strrev($item);
        $tmp = explode(' ', $tmp, 4);
        $dic_previous_count[strrev($tmp[3])] = strrev($tmp[0]);
    }

    $content = '';
    foreach (array_keys($playlist) as $url) {
        $data = $playlist[$url];
        if (array_key_exists($data['title'], $dic_previous_count)) {
            $content .= $data['title'] . ' ' . $dic_previous_count[$data['title']] . ' → ' . $data['count'] . "\n";
        } else {
            $content .= $data['title'] . ' 0 → ' . $data['count'] . "\n";
        }
    }
    error_log($log_prefix . $content);
    $mu_->post_blog_wordpress('Play Count', $content);
}
