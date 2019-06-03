<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// set_time_limit(60);

exec('ls -lang');

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
        $tmpdir = microtime(true);
        exec('mkdir /tmp/' . $tmpdir);
        $tmpdir = '/tmp/' . $tmpdir . '/';
        if (move_uploaded_file($upload_file['tmp_name'], $tmpdir . $upload_file['name'])) {
            $filesize = filesize($tmpdir . $upload_file['name']);
            error_log('filesize : ' . $filesize);
            exec('cd ' . $tmpdir . ' && /app/bin/unrar x ./' . $upload_file['name']);
            unlink($tmpdir . $upload_file['name']);
            exec('ls -lang ' . $tmpdir . ' >>/tmp/log.txt');
            error_log(file_get_contents('/tmp/log.txt'));
            $download_file_name = pathinfo($upload_file['name'],  PATHINFO_FILENAME) . '.zip';
            exec('cd ' . $tmpdir . ' && zip -r ' . $download_file_name . ' ./');
            header('Content-Transfer-Encoding: binary');
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $download_file_name . '"');
            echo file_get_contents($tmpdir . $download_file_name);
            unlink($tmpdir . $download_file_name);
        }
    }
} else {
    echo $html;
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
