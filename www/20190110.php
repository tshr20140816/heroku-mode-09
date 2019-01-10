<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

require_once 'XML/RPC2/Client.php';

/*
$url = 'https://blog.fc2.com/xmlrpc.php';
error_log($url);
$client = XML_RPC2_Client::create(
  $url,
  ['prefix' => 'mt.']
);

$result = $client->supportedMethods('', getenv('FC2_ID'), getenv('FC2_PASSWORD'));
error_log(print_r($result, true));
*/

/*
$post_data = '<?xml version="1.0" ?><methodCall><methodName>mt.supportedMethods</methodName>'
  . '<params><param><value><string></string></value></param><param><value>'
  . '<string>' . getenv('FC2_ID') . '</string></value></param><param><value>'
  . '<string>' . getenv('FC2_PASSWORD') . '</string></value></param></params></methodCall>';

$options = [CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/xml'],
            ];
$res = $mu->get_contents_nocache('http://blog.fc2.com/xmlrpc.php', $options);

error_log($res);
*/

$url = 'https://' . getenv('WORDPRESS_USERNAME') . '.wordpress.com/xmlrpc.php';
error_log($url);
$client = XML_RPC2_Client::create(
  $url,
  ['prefix' => 'wp.']
);

$result = $client->getUsersBlogs('', getenv('WORDPRESS_USERNAME'), getenv('WORDPRESS_PASSWORD'));
error_log(print_r($result, true));

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
