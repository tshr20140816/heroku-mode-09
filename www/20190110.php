<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

require_once 'XML/RPC2/Client.php';
  
$options = array(
  'prefix' => 'mt.'
);

$client = XML_RPC2_Client::create(
  'https://blog.fc2.com/xmlrpc.php',
  $options
);

$result = $client->supportedMethods('', getenv('FC2_ID'), getenv('FC2_PASSWORD'));
error_log(print_r($result, true));

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

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
