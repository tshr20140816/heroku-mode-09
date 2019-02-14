<?php

$url = $_GET['u'];

$ch = curl_init();

$options = [
    CURLOPT_URL => $url,
    CURLOPT_USERAGENT => getenv('USER_AGENT'),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_FOLLOWLOCATION => 1,
    CURLOPT_MAXREDIRS => 3,
    CURLOPT_PATH_AS_IS => true,
    CURLOPT_TCP_FASTOPEN => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HEADER => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
];

foreach ($options as $key => $value) {
    $rc = curl_setopt($ch, $key, $value);
    if ($rc == false) {
        error_log("curl_setopt : ${key} ${value}");
    }
}

$res = curl_exec($ch);
$info = curl_getinfo($ch);

error_log(substr($res, 0, $info['header_size']));
