<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$rc = apcu_clear_cache();
$mu = new MyUtils();

func_20190331($mu, '/tmp/dummy');

function func_20190331($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    file_put_contents('/tmp/dummy1.txt', 'DUMMY');
    
    $user_cloudapp = getenv('CLOUDAPP_USER');
    $user_cloudpassword = getenv('CLOUDAPP_PASSWORD');
    
    $url = 'http://my.cl.ly/items?per_page=1000&page=1';
        
    $res = $mu_->get_contents(
        $url,
        [CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
         CURLOPT_USERPWD => "${user_cloudapp}:${user_cloudpassword}",
         CURLOPT_HTTPHEADER => ['Accept: application/json',],
        ]
    );
    error_log(print_r(json_decode($res), true));
    
    return;
    
    $url = 'http://my.cl.ly/account';
    $url = 'http://my.cl.ly/account/stats';
    $url = 'http://my.cl.ly/items/new';
        
    $res = $mu_->get_contents(
        $url,
        [CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
         CURLOPT_USERPWD => "${user_cloudapp}:${user_cloudpassword}",
         CURLOPT_HTTPHEADER => ['Accept: application/json',],
        ]
    );
    error_log(print_r(json_decode($res), true));
    $json = json_decode($res);
        
    $url = $json->url;
    $post_data = ['AWSAccessKeyId' => $json->params->AWSAccessKeyId,
                  'key' => $json->params->key,
                  'policy' => $json->params->policy,
                  'signature' => $json->params->signature,
                  'success_action_redirect' => $json->params->success_action_redirect,
                  'acl' => $json->params->acl,
                  'file' => new CURLFile('/tmp/dummy1.txt', 'text/plain', 'dummy1.txt'),
                 ];
    
    $res = $mu_->get_contents(
        $url,
        [CURLOPT_POST => true,
         CURLOPT_POSTFIELDS => $post_data,
         CURLOPT_HEADER => true,
         CURLOPT_FOLLOWLOCATION => false,
        ]);
    
    error_log($res);
    
    $rc = preg_match('/Location: (.+)/i', $res, $match);
    error_log(print_r($match, true));
    
    $url = trim($match[1]);
    $res = $mu_->get_contents(
        $url,
        [CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
         CURLOPT_USERPWD => "${user_cloudapp}:${user_cloudpassword}",
         CURLOPT_HTTPHEADER => ['Accept: application/json',],
        ]
    );
    error_log(print_r(json_decode($res), true));
}
