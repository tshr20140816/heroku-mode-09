<?php

$html = <<< __HEREDOC__
<html>
<head>
<title>__TITLE__</title>
</head>
<body>
<table>
__BODY__
</table>
</body></html>
__HEREDOC__;

$url = 'https://search.travel.rakuten.co.jp/ds/hotellist/Japan-Mie-Tsu-low?f_nen1=2019&f_tuki1=10&f_hi1=11&f_nen2=2019&f_tuki2=10&f_hi2=12&f_otona_su=2&f_s1=0&f_s2=0&f_y1=0&f_y2=0&f_y3=0&f_y4=0&f_heya_su=1&f_kin2=0&f_ido=0&f_kdo=0&f_km=7.0&f_hyoji=30&f_image=1&f_tab=hotel&f_datumType=WGS&f_point_min=0';

$res = get_contents($url);

error_log($res);

function get_contents($url_, $options_ = null)
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
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1',
        ],
    ];
    
    $ch = curl_init();
    foreach ($options as $key => $value) {
        $rc = curl_setopt($ch, $key, $value);
        if ($rc == false) {
            error_log($log_prefix . "curl_setopt : ${key} ${value}");
        }
    }
    if (is_null($options_) === false) {
        foreach ($options_ as $key => $value) {
            $rc = curl_setopt($ch, $key, $value);
            if ($rc == false) {
                error_log($log_prefix . "curl_setopt : ${key} ${value}");
            }
        }
    }
    $res = curl_exec($ch);
    $http_code = (string)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $res;
}
