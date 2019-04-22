<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$img = '<![CDATA[<img src="data:image/png;base64,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" />]]>';

