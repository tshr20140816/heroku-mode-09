<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

require_once 'XML_RPC2/Client.php';
//require_once 'XML/RPC2/Client.php';
  
$client = XML_RPC2_Client('http://xmlrpc.example.com/1.0/', 'example.');

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
