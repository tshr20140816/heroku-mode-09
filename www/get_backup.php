<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

error_log("${pid} " . print_r(getallheaders(), true));

$html = <<< __HEREDOC__
<html><body>
<form method="POST" action="./get_backup.php">
<input type="text" name="file_name" />
<input type="submit" /> 
</form>
</body></html>
__HEREDOC__;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $file_name = $_POST['file_name'];
    $user = base64_decode(getenv('HIDRIVE_USER'));
    $password = base64_decode(getenv('HIDRIVE_PASSWORD'));
    $url = "https://webdav.hidrive.strato.com/users/${user}/${file_name}";
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "${user}:${password}",
        CURLOPT_CUSTOMREQUEST => 'GET',
    ];
    $res = $mu->get_contents($url, $options);
    
    $res = base64_decode($res);
    
    error_log($pid . ' base64_decode : ' . strlen($res));
    
    $method = 'AES-256-CBC';
    $password = base64_encode(getenv('HIDRIVE_USER')) . base64_encode(getenv('HIDRIVE_PASSWORD'));
    $IV = substr(sha1("/tmp/${file_name}"), 0, openssl_cipher_iv_length($method));
    $res = openssl_decrypt($res, $method, $password, OPENSSL_RAW_DATA, $IV);
    
    error_log($pid . ' openssl_decrypt : ' . strlen($res));
    
    $res = bzdecompress($res);
    
    error_log($pid . ' bzdecompress : ' . strlen($res));
    
    // error_log($res);
    file_put_contents("/tmp/${file_name}", $res);
    
    $zip_file = '/tmp/' . pathinfo($file_name)['filename'] . '.zip';
    $password = base64_encode(getenv('ZIP_PASSWORD'));
    system("zip -j -P ${password} ${zip_file} /tmp/${file_name}");
    
    header('Content-Type: application/zip');
    echo file_get_contents($zip_file);
    
    unlink('/tmp/' . pathinfo($file_name)['filename']);
    unlink("/tmp/${file_name}");
} else {
    echo $html;
}

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's');
