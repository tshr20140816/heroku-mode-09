<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$url = $mu->get_env('URL_KASA_SHISU_YAHOO');

$list = make_curl_multi($url);

$res = get_curl_multi($list[$url]);

error_log(getmypid() . ' FINISH');

function make_curl_multi($url_)
{
    $mh = curl_multi_init();

    $ch = curl_init();
    $options = [CURLOPT_URL => $url_,
                CURLOPT_USERAGENT => getenv('USER_AGENT'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_SSL_FALSESTART => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 10,
    ];
    curl_setopt_array($ch, $options);
    curl_multi_add_handle($mh, $ch);

    $active = null;
    do {
        $rc = curl_multi_exec($mh, $active);
    } while ($rc == CURLM_CALL_MULTI_PERFORM);
    
    $list_curl_multi_info[$url_]['multi_handle'] = $mh;
    $list_curl_multi_info[$url_]['channel'] = $ch;
    $list_curl_multi_info[$url_]['rc'] = $rc;
    $list_curl_multi_info[$url_]['active'] = $active;
    
    error_log(getmypid() . ' [' . __METHOD__ . '] $list_curl_multi_info : ' . print_r($list_curl_multi_info, true));
    
    return $list_curl_multi_info;
}

function get_curl_multi($list_)
{
    error_log(__METHOD__);
        
    $active = $list_['active'];
    $rc = $list_['rc'];
    $ch = $list_['channel'];
    $mh = $list_['multi_handle'];
    
    while ($active && $rc == CURLM_OK) {
        if (curl_multi_select($mh) == -1) {
            usleep(1);
        }
        do {
            $rc = curl_multi_exec($mh, $active);
        } while ($rc == CURLM_CALL_MULTI_PERFORM);
    }

    $results = curl_getinfo($ch);
    $res = curl_multi_getcontent($ch);
    curl_multi_remove_handle($mh, $ch);
    curl_close($ch);

    error_log(print_r($results, true));

    curl_multi_close($mh);
    
    return $res;
}
