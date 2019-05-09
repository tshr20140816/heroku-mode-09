<?php

$res = get_file_contents('/proc/cpuinfo');

error_log($res);
