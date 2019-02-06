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

file_put_contents($file_name);


$filename = 'useragent.txt';
$filepath = '/app/' . $filename;

$filesize = filesize($filepath);
error_log($filesize);
$fh = fopen($filepath, 'r');

/*
$ch = curl_init('https://webdav.hidrive.strato.com/users/' . getenv('HIDRIVE_USER'). '/test4.txt');

curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($ch, CURLOPT_USERPWD, getenv('HIDRIVE_USER') . ':' . getenv('HIDRIVE_PASSWORD'));
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_PUT, true);
curl_setopt($ch, CURLOPT_INFILE, $fh);
curl_setopt($ch, CURLOPT_INFILESIZE, $filesize);

$res = curl_exec($ch);
*/

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
