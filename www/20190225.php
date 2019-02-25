<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = 'https://secure.reservation.jp/sanco-inn/stay_pc/rsv/rsv_src_pln.aspx?cond=or&dt_tbd=0&le=1&rc=1&pmin=0&ra=&pa=&cl_tbd=0&mc=2&rt=4%3a8&st=0&pmax=2147483647&cc=&smc_id=&hi_id=10&dt=2019/8/25&lang=ja-JP';

$res = $mu->get_contents($url);

error_log($res);
