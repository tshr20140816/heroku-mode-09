<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$mu = new MyUtils();

$url = 'https://weather.yahoo.co.jp/weather/jp/27/6200.html';
$res = $mu->get_contents($url);

error_log($res);

?>
