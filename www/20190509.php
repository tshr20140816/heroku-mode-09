<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$mu = new MyUtils();

error_log($mu->get_env('FC2_FTP_ID', true));
error_log($mu->get_env('FC2_FTP_SERVER', true));
error_log($mu->get_env('FC2_ID', true));
