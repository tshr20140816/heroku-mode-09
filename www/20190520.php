<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$mu = new MyUtils();

error_log($mu->get_encrypt_string(getenv('TEST_ID')));
