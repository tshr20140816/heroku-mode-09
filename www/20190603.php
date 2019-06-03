<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// set_time_limit(60);

exec('/app/bin/unrar --version');

$html = <<< __HEREDOC__
<html><body>
<form method="POST" action="./20190603.php" enctype="multipart/form-data">
<input type="file" name="upload_file" />
<input type="submit" /> 
</form>
</body></html>
__HEREDOC__;

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $upload_file = $_FILES['upload_file'];
    if (is_uploaded_file($upload_file['tmp_name'])) {
        if (move_uploaded_file($upload_file['tmp_name'], '/tmp/' . $upload_file['name'])) {
            $filesize = filesize('/tmp/' . $upload_file['name']);
            error_log('filesize : ' . $filesize);
        }
    }
} else {
    echo $html;
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
