<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190425($mu_, '/tmp/dummy');

function func_20190425($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $api_key = base64_decode(getenv('HEROKU_API_KEY'));
    $url = 'https://api.heroku.com/account';

    $res = $mu_->get_contents(
        $url,
        [CURLOPT_HTTPHEADER => ['Accept: application/vnd.heroku+json; version=3',
                                "Authorization: Bearer ${api_key}",
                               ]],
        true
    );
}
