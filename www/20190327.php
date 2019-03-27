<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$rc = func_test3($mu, '/tmp/dummy');
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test3($mu_, $file_name_blog_)
{
    $livedoor_id = $this->get_env('LIVEDOOR_ID', true);
    $livedoor_atom_password = $this->get_env('LIVEDOOR_ATOM_PASSWORD', true);
}
