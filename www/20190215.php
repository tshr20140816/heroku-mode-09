<?php

$rc = opcache_compile_file('./hourly.php');

error_log($rc);

