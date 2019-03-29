<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

func2019329($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func2019329($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $client = new SoapClient('https://api.4shared.com/jax2/DesktopApp?wsdl');
    
    error_log(print_r($client->__getFunctions()));
}
