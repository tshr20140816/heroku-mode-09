<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

error_log($pid . ' '. print_r(getallheaders(), true));

$html = <<< __HEREDOC__
<html><body>
<form method="POST" action="./base64encode.php">
<input type="text" name="target_text" />
<input type="submit" /> 
</form>
</body></html>
__HEREDOC__;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_text = $_POST['target_text'];
    error_log("${pid} ${target_text}");
    header('Content-Type: text/plain');
    echo base64_encode($target_text);
} else {
    echo $html;
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
