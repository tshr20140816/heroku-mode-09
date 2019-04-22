<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$mu = new MyUtils();

$url = 'https://ja.wikipedia.org/wiki/Wikipedia:%E4%BB%8A%E6%97%A5%E3%81%AF%E4%BD%95%E3%81%AE%E6%97%A5';

$res = $mu->get_contents($url);

$tmp = explode('<h2>', $res, 3);

// error_log($tmp[1]);

$rc = preg_match_all('/<li>(.+?)<\/li>/s', explode('<h2>', $res, 3)[1], $matches);

error_log(print_r($matches, true));
