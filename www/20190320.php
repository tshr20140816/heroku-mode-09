<?php

$url = 'https://www.iwakuni-kanko.com/festival/0505/';

function get_contents_nocache($url_, $options_ = null)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    error_log($log_prefix . 'URL : ' . $url_);
    error_log($log_prefix . 'options : ' . print_r($options_, true));
    $options = [
        CURLOPT_URL => $url_,
        CURLOPT_USERAGENT => getenv('USER_AGENT'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_PATH_AS_IS => true,
        CURLOPT_TCP_FASTOPEN => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
    ];
}
