<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

$SOAP_HEADER = <<< __HEREDOC__
<SOAP-ENV:Envelope
 xmlns:SOAPENV="http://schemas.xmlsoap.org/soap/envelope/"
 SOAP-ENV:encodingStyle=""
 xmlns:xsi="http://www.w3.org/1999/XMLSchema-instance"
 xmlns:xsd="http://www.w3.org/1999/XMLSchema">
<SOAP-ENV:Body>
__HEREDOC__;

$SOAP_FOOTER = <<< __HEREDOC__
</SOAP-ENV:Body></SOAP-ENV:Envelope>
__HEREDOC__;

$url = 'https://www.cloudme.com/v1/';

$user_cloudme = getenv('CLOUDME_USER');
$password_cloudme = getenv('CLOUDME_PASSWORD');

error_log($mu->get_encrypt_string($user_cloudme));
error_log($mu->get_encrypt_string($password_cloudme));

$action = 'login';
$body = '';

$post_data = $SOAP_HEADER . "<${action}></${action}>" . $SOAP_FOOTER;

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_HEADER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post_data,
    CURLOPT_HTTPHEADER => ["soapaction: ${action}",
                           'Content-Type: text/xml; charset=utf-8',
                          ],
];

$res = $mu->get_contents($url, $options);

error_log($res);

// exit();

//$user_cloudme = getenv('CLOUDME_USER');
//$password_cloudme = getenv('CLOUDME_PASSWORD');

$url = "https://webdav.cloudme.com/${user_cloudme}/xios";

$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_HEADER => true,
];

//$res = $mu->get_contents($url, $options);

error_log($res);

$file_name = '/tmp/dummy2.txt';
file_put_contents($file_name, 'DUMMY');

$file_size = filesize($file_name);
$fh = fopen($file_name, 'r');

$url = "https://webdav.cloudme.com/${user_cloudme}/xios/" . pathinfo($file_name)['basename'];
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_PUT => true,
    CURLOPT_INFILE => $fh,
    CURLOPT_INFILESIZE => $file_size,
    CURLOPT_HEADER => true,
];
$res = $mu->get_contents($url, $options);

fclose($fh);
@unlink($cookie);

$url = "https://webdav.cloudme.com/${user_cloudme}/xios/" . pathinfo($file_name)['basename'];
$options = [
    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
    CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_HEADER => true,
];
$res = $mu->get_contents($url, $options);
