<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

error_log(openssl_cipher_iv_length('AES-256-CBC'));

exit;

$file_name = '/tmp/pg_dump.dat';
$cmd = 'pg_dump --format=plain --dbname=' . getenv('DATABASE_URL') . ' >' . $file_name;
exec($cmd);

$res = bzcompress(file_get_contents($file_name), 9);
$res = openssl_encrypt($res, 'AES-256-CBC', getenv('BACKUP_PASSWORD'), OPENSSL_RAW_DATA, '0123456789012345');


$filename = 'useragent.txt';
$filepath = '/app/' . $filename;

$filesize = filesize($filepath);
error_log($filesize);
$fh = fopen($filepath, 'r');

$ch = curl_init('https://webdav.hidrive.strato.com/users/' . getenv('HIDRIVE_USER'). '/test3.txt');

curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_USERPWD, getenv('HIDRIVE_USER') . ':' . getenv('HIDRIVE_PASSWORD'));
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_PUT, true);
curl_setopt($ch, CURLOPT_INFILE, $fh);
curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);

$res = curl_exec($ch);

fclose($fh);

error_log(print_r($res, true));

