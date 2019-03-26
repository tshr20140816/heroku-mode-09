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

//$url = 'https://secure.reservation.jp/sanco-inn/stay_pc/rsv/rsv_src_pln.aspx?cond=or&dt_tbd=0&le=1&rc=1&pmin=0&ra=&pa=&cl_tbd=0&mc=2&rt=&st=0&pmax=2147483647&cc=&smc_id=&hi_id=10&dt=2019/10/05&lang=ja-JP';

$url = 'https://feed43.com/tsu20191011.xml';

$res = get_contents($url, [CURLOPT_HEADER => true]);

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
