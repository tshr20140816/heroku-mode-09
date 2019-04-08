<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

func_20190408($mu, '/tmp/dummy');

function func_20190408($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://baseball.yahoo.co.jp/npb/schedule/?date=20190329';
    
    // $options = [CURLOPT_HEADER => true,];
    // $res = $mu_->get_contents($url, $options);
    $res = $mu_->get_contents($url);

    $pattern = '<table border="0" cellspacing="0" cellpadding="0" class="teams">.+?';
    $pattern .= '<table border="0" cellspacing="0" cellpadding="0" class="score">.+?';
    $pattern .= '<a href="https:\/\/baseball.yahoo.co.jp\/npb\/game\/(\d+)\/".+?<\/table>.+?<\/table>';
    $rc = preg_match_all('/' . $pattern . '/s', $res, $matches, PREG_SET_ORDER);
    
    // error_log(print_r($matches, true));
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
    //error_log($res);
    
    $tmp = explode('</table>', $res);
    
    foreach ($tmp as $data) {
        if (strpos($data, '野間 峻祥') > 0) {
            $rc = preg_match_all('/<tr.*?>(.+?)<\/tr>/s', $data, $matches);
            foreach ($matches[1] as $item) {
                if (strpos($item, '野間 峻祥') > 0) {
                    error_log(strip_tags($item));
                    break 2;
                }
            }
        }
    }    
}
