<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$rc = func_20190611($mu);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function func_20190611($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://twitter.com/JAXA_jp';
    $res = $mu_->get_contents($url);
    
    $tweets = explode('<div class="js-tweet-text-container">', $res);
    array_shift($tweets);
    
    $rss_item = <<< __HEREDOC__
<item>
<guid isPermaLink="false">__HASH__</guid>
<pubDate>__PUBDATE__</pubDate>
<title>__TITLE__</title>
<link>http://dummy.local/</link>
<description>__DESCRIPTION__</description>
</item>
__HEREDOC__;
    
    $rss_items = [];
    foreach ($tweets as $one_tweet) {
        $rc = preg_match('/<p .+?>(.+?)<.+?<img data-aria-label-part src="(.+?)".+?data-time="(.+?)"/s', $one_tweet, $match);
        array_shift($match);
        if (count($match) === 0) {
            continue;
        }
        error_log(print_r($match, true));
        $url = $match[1];
        
        $res = $mu_->get_contents($url);
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $filename = tempnam('/tmp', 'image_' . md5(microtime(true)));
        file_put_contents($filename, $res);
        $rc = getimagesize($filename);
        error_log(print_r($rc, true));
        unlink($filename);
        if (array_key_exists('mime', $rc)) {
            $extension = explode('/', $rc['mime'])[1];
        }
        $description = '<img src="data:image/' . $extension . ';base64,' . base64_encode($res) . '" />';
        
        $tmp = str_replace('__DESCRIPTION__', $description, $rss_item);
        $tmp = str_replace('__TITLE__', htmlspecialchars($match[0]), $tmp);
        $tmp = str_replace('__PUBDATE__', date('D, j M Y G:i:s +0900', $match[2]), $tmp);
        $tmp = str_replace('__HASH__', hash('sha256', $description), $tmp);
        
        if ((strlen(implode('', $rss_items)) + strlen($tmp)) > 900000) {
            break;
        }
        
        $rss_items[] = $tmp;
    }
    
    $xml_text = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
<title>jaxa</title>
<link>http://dummy.local/</link>
<description>jaxa</description>
__ITEMS__
</channel>
</rss>
__HEREDOC__;
    
    $file = '/tmp/' . 'test.xml';
    file_put_contents($file, str_replace('__ITEMS__', implode('', $rss_items), $xml_text));
    $mu_->upload_fc2($file);
    error_log('filesize : ' . filesize($file));
    unlink($file);
}
