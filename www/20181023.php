<?php

$res = file_get_contents('https://eco.mtk.nao.ac.jp/koyomi/dni/2018/m2011.html');

$tmp = explode('</th>', end($res));
$tmp = explode('</table>', $tmp[1]);
$tmp = explode('</tr>', $tmp[0]);

error_log(print_r($tmp, TRUE));

?>
