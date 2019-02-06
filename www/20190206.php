<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_pg_dump.txt';
error_log($file_name);
$cmd = 'pg_dump --format=plain --dbname=' . getenv('DATABASE_URL') . ' >' . $file_name;
exec($cmd);

$res = bzcompress(file_get_contents($file_name), 9);

$method = 'AES-256-CBC';
$password = getenv('BACKUP_PASSWORD');
$IV = substr(sha1($file_name), 0, openssl_cipher_iv_length($method));
$res = openssl_encrypt($res, $method, $password, OPENSSL_RAW_DATA, $IV);

$res = base64_encode($res);

error_log(strlen($res));

file_put_contents($file_name, $res);

$url = 'https://webdav.hidrive.strato.com/users/' . getenv('HIDRIVE_USER') . '/' . pathinfo($file_name)['basename'];
error_log($url);
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_USERPWD => getenv('HIDRIVE_USER') . ':' . getenv('HIDRIVE_PASSWORD'),
    CURLOPT_CUSTOMREQUEST => 'DELETE',
];
$res = $mu->get_contents($url, $options);

$fh = fopen($file_name, 'r');

$url = 'https://webdav.hidrive.strato.com/users/' . getenv('HIDRIVE_USER') . '/' . pathinfo($file_name)['basename'];
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_USERPWD => getenv('HIDRIVE_USER') . ':' . getenv('HIDRIVE_PASSWORD'),
    CURLOPT_PUT => true,
    CURLOPT_INFILE => $fh,
    CURLOPT_INFILESIZE => filesize($file_name),
];

$res = $mu->get_contents($url, $options);
    
fclose($fh);

@unlink($file_name);
