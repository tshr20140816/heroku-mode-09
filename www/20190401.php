<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$rc = apcu_clear_cache();
$mu = new MyUtils();

func_20190401($mu, '/tmp/dummy');

function func_20190401($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $user_zoho = getenv('ZOHO_USER');
    $password_zoho = getenv('ZOHO_PASSWORD');
    $url = 'https://accounts.zoho.com/apiauthtoken/nb/create';
    
    $post_data = ['SCOPE' => 'ZohoPC/docsapi',
                  'EMAIL_ID' => $user_zoho,
                  'PASSWORD' => $password_zoho,
                  'DISPLAY_NAME' => 'ZOHODOCS',
                 ];
    
    $res = $mu_->get_contents(
        $url,
        [CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
        ]
    );
    
    error_log($res);
}
