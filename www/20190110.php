<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

/*
require_once 'XML/RPC2/Client.php';
  
$options = array(
  'methodName' => 'mt.supportedMethods'
);

$client = XML_RPC2_Client::create(
  'http://blog.fc2.com/xmlrpc.php',
  $options
);

$result = $client->info('mt.supportedMethods');
error_log(print_r($result, true));
*/

//<methodCall><methodName>mt.supportedMethods</methodName><params></params></methodCall>

$options = [CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => '<methodCall><methodName>mt.supportedMethods</methodName><params></params></methodCall>',
            ];

$res = $mu->get_contents_nocache('http://blog.fc2.com/xmlrpc.php', $options);

error_log($res);

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
