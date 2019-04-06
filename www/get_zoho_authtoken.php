<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// get_zoho_authtoken($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function get_zoho_authtoken($mu_)
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
    
    error_log($log_prefix . $res);
    $rc = preg_match('/AUTHTOKEN=(.+)/', $res, $match);
    error_log($log_prefix . print_r($match, true));
    
    error_log($log_prefix . $mu_->get_encrypt_string($match[1]));
}
