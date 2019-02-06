<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name = '/tmp/pg_dump.dat';
$cmd = 'pg_dump --format=plain --dbname=' . getenv('DATABASE_URL') . ' >' . $file_name;
exec($cmd);

$res = bzcompress(file_get_contents($file_name), 9);

$method = 'AES-256-CBC';
$password = getenv('BACKUP_PASSWORD');
$IV = substr(sha1($password), 0, openssl_cipher_iv_length($method));
$res = openssl_encrypt($res, $method, $password, OPENSSL_RAW_DATA, $IV);

$res = base64_encode($res);

error_log(strlen($res));

file_put_contents($file_name, $res);


$url = 'https://webdav.hidrive.strato.com/users/' . getenv('HIDRIVE_USER'). '/test4.txt';
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_USERPWD => getenv('HIDRIVE_USER') . ':' . getenv('HIDRIVE_PASSWORD'),
    CURLOPT_CUSTOMREQUEST => 'DELETE',
];
$res = $mu->get_contents($url, $options);

$filename = 'useragent.txt';
$filepath = '/app/' . $filename;

$filesize = filesize($filepath);
error_log($filesize);
$fh = fopen($filepath, 'r');

$url = 'https://webdav.hidrive.strato.com/users/' . getenv('HIDRIVE_USER'). '/test4.txt';
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_ANY,
    CURLOPT_USERPWD => getenv('HIDRIVE_USER') . ':' . getenv('HIDRIVE_PASSWORD'),
    CURLOPT_PUT => true,
    CURLOPT_INFILE => $fh,
    CURLOPT_INFILESIZE => $filesize,
];

$res = $mu->get_contents($url, $options);

    
fclose($fh);

error_log(print_r($res, true));

@unlink($file_name);
