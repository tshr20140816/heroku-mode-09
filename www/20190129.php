<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

check_lib($mu);

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');

function check_lib($mu_) {

    $cookie = $tmpfname = tempnam("/tmp", time());

    $options1 = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
    ];
    
    $url = getenv('LIB_URL');
    $res = $mu_->get_contents($url, $options1);
    
    error_log($res);

    $rc = preg_match('/<form name="LoginForm" method="post" action="(.+?)"/', $res, $match);
    
    error_log(print_r($match, true));
    
    $url = 'https://' . parse_url(getenv('LIB_URL'))['host'] . $match[1];
    
    $post_data = [
        'txt_usercd' => getenv('LIB_ID'),
        'txt_password' => getenv('LIB_PASSWORD'),
        'submit_btn_login' => 'ログイン',
        ];
    
    $options2 = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
    ];
    
    $res = $mu_->get_contents($url, $options2);
    
    error_log($res);
    
    $url = 'https://' . parse_url(getenv('LIB_URL'))['host'] . '/winj/opac/reserve-list.do';
    $res = $mu_->get_contents($url, $options1);
    
    error_log($res);
    
    unlink($cookie);
}
