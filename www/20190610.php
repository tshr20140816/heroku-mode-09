<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$rc = func_20190610($mu);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function func_20190610($mu_)
{
    $url = 'https://twitter.com/bs_ponta';
    $res = $mu_->get_contents($url);
    // error_log($res);
    
    $tmp = explode('<div class="js-tweet-text-container">', $res);
    array_shift($tmp);
    // error_log(print_r($tmp, true));
    
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
    foreach ($tmp as $one_tweet) {
        $rc = preg_match('/<p .+?>(.+?)<.+?<img data-aria-label-part src="(.+?)".+?data-time="(.+?)"/s', $one_tweet, $match);
        array_shift($match);
        if (count($match) === 0) {
            continue;
        }
        error_log(print_r($match, true));
        
        $res = $mu_->get_contents($match[1]);
        error_log('original size : ' . filesize($res));
        error_log('imagecreatefromjpeg size : ' . imagecreatefromjpeg($match[1]));
        $description = '<img src="data:image/jpg;base64,' . base64_encode($res) . '" />';
        
        $tmp1 = str_replace('__DESCRIPTION__', $description, $rss_item);
        $tmp1 = str_replace('__TITLE__', $match[0], $tmp1);
        $tmp1 = str_replace('__PUBDATE__', date('D, j M Y G:i:s +0900', $match[2]), $tmp1);
        $tmp1 = str_replace('__HASH__', hash('sha256', $description), $tmp1);
        
        $rss_items[] = $tmp1;
    }
    
    $xml_text = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
<title>bs_ponta</title>
<link>http://dummy.local/</link>
<description>bs_ponta</description>
__ITEMS__
</channel>
</rss>
__HEREDOC__;
    
    $file = '/tmp/' . getenv('FC2_RSS_02') . '.xml';
    file_put_contents($file, str_replace('__ITEMS__', implode('', $rss_items), $xml_text));
    $mu_->upload_fc2($file);
    error_log('filesize : ' . filesize($file));
    unlink($file);
}
