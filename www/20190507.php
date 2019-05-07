<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190507($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190507($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'https://baseball.yahoo.co.jp/npb/standings/';
    $res = $mu_->get_contents($url);
    
    $tmp = explode('<table class="NpbPlSt yjM">', $res);
    // error_log($tmp[1]);
    
    $rc = preg_match_all('/title="(.+?)"/', $tmp[1] . $tmp[2], $matches);
    
    // error_log(print_r($matches, true));
    $list_team = $matches[1];
    
    $rc = preg_match_all('/<td>(.+?)</', $tmp[1] . $tmp[2], $matches);
    
    // error_log(print_r($matches, true));
    
    for ($i = 0; $i < 12; $i++) {
        // error_log($matches[1][$i * 13 + 7]);
        $list_team[$i] = $list_team[$i] . ',' . $matches[1][$i * 13 + 7] . ',' . $matches[1][$i * 13 + 8];
        // $base_data[] = '{x:' . $matches[1][$i * 13 + 7] . ',y:' . $matches[1][$i * 13 + 8] . '}';
        $tmp1 = null;
        $tmp1->x = $matches[1][$i * 13 + 7];
        $tmp1->y = $matches[1][$i * 13 + 8];
        $tmp2 = [];
        $tmp2['label'] = $list_team[$i];
        $tmp2['data'] = $tmp1;
        $datasets[] = $tmp2;
    }
    error_log(print_r($datasets, true));
    
    /*
    $data = '{"type":"scatter","data":{"datasets":[{"data":[' . implode(',', $base_data) .']}]}}';
    

    $data = ['type' => 'line',
             'data' => $data,
            ];
    $url = 'https://quickchart.io/chart?width=600&height=320&c=' . json_encode($data);
    $res = $mu_->get_contents($url);
    
    header('Content-Type: image/png');
    echo $res;
    
    error_log(print_r(json_decode('{"type":"scatter","data":{"datasets":[{"label":"A","data":[{"x":160,"y":116}]},{"label":"B","data":[{"x":171,"y":146}]}]}}'), true));
    */
}
