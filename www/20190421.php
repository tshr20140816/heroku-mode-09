<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$mu = new MyUtils();

$url = 'https://ja.wikipedia.org/wiki/Wikipedia:%E4%BB%8A%E6%97%A5%E3%81%AF%E4%BD%95%E3%81%AE%E6%97%A5';

$res = $mu->get_contents($url);

$tmp = explode('<h2>', $res, 3);

$rc = preg_match_all('/<li>(.+?)<\/li>/s', explode('<h2>', $res, 3)[1], $matches);

$html = <<< __HEREDOC__
<html><head><title>test</title></head><body>__BODY__</body></html>
__HEREDOC__;

$html = str_replace('__BODY__', trim(strip_tags($matches[1][rand(0, count($matches[1]) - 1)])), $html);

error_log($html);

$file_name = '/tmp/index.html';
file_put_contents($file_name, $html);

$mu->upload_fc2($file_name);
