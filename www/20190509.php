<?php

$res = file_get_contents('/proc/cpuinfo');

error_log($res);
