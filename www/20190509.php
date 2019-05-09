<?php

$res = file_get_contents('/proc/cpuinfo');

$rc = preg_match('/model name.*?:\s*(.+)/', $res, $match);

error_log(print_r($match, true));
