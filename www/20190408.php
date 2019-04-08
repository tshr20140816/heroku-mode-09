<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190408($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190408($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $livedoor_id = $mu_->get_env('LIVEDOOR_ID', true);
    $url = "http://blog.livedoor.jp/${livedoor_id}/search?q=NOMA+Takayoshi";
    $res = $mu_->get_contents($url);
    
    $rc = preg_match('/<div class="article-body-inner">(.+?)<\/div>/s', $res, $match);
    $base_record = trim(strip_tags($match[1]));
    error_log($log_prefix . $base_record);
    
    $name = '野間 峻祥';
    $title = 'NOMA Takayoshi';
    $timestamp = strtotime('-1 day');
    
    if (strpos($base_record, date('Y/m/d', $timestamp)) > 0) {
        return;
    }
    error_log(date('Y/m/d', $timestamp));
    
    $ymd = date('Ymd', $timestamp);
    $url = 'https://baseball.yahoo.co.jp/npb/schedule/?date=' . $ymd;
    
    $res = $mu_->get_contents($url);

    $pattern = '<table border="0" cellspacing="0" cellpadding="0" class="teams">.+?';
    $pattern .= '<table border="0" cellspacing="0" cellpadding="0" class="score">.+?';
    $pattern .= '<a href="https:\/\/baseball.yahoo.co.jp\/npb\/game\/(\d+)\/".+?<\/table>.+?<\/table>';
    $rc = preg_match_all('/' . $pattern . '/s', $res, $matches, PREG_SET_ORDER);

    $url = '';
    foreach ($matches as $match) {
        if (strpos($match[0], '広島') > 0) {
            $url = 'https://baseball.yahoo.co.jp/npb/game/' . $match[1] . '/stats';
            break;
        }
    }

    if ($url == '') {
        return;
    }
    $res = $mu_->get_contents($url);

    $tmp = explode('</table>', $res);

    foreach ($tmp as $data) {
        if (strpos($data, $name) > 0) {
            $rc = preg_match_all('/<tr.*?>(.+?)<\/tr>/s', $data, $matches);
            foreach ($matches[1] as $item) {
                if (strpos($item, $name) > 0) {
                    $tmp = str_replace("\n", '', $item);
                    $tmp = preg_replace('/<.+?>/s', ' ', $tmp);
                    $tmp = str_replace($name, '', $tmp);
                    $tmp = date('Y/m/d', $timestamp) . ' ' . trim(preg_replace('/ +/', ' ', $tmp));
                    $description = $tmp . "\n" . $base_record;
                    error_log($log_prefix . $description);
                    // $mu_->post_blog_wordpress($title, $description);
                    break 2;
                }
            }
        }
    }
}
