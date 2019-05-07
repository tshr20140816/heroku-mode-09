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

    $color_index['広島'] = 'red';
    $color_index['ヤクルト'] = 'cyan';
    $color_index['巨人'] = 'orange';
    $color_index['ＤｅＮＡ'] = 'blue';
    $color_index['中日'] = 'dodgerblue';
    $color_index['阪神'] = 'yellow';
    $color_index['西武'] = 'black';
    $color_index['ソフトバンク'] = 'black';
    $color_index['日本ハム'] = 'black';
    $color_index['オリックス'] = 'black';
    $color_index['ロッテ'] = 'black';
    $color_index['楽天'] = 'black';
    
    $url = 'https://baseball.yahoo.co.jp/npb/standings/';
    $res = $mu_->get_contents($url);
    
    $tmp = explode('<table class="NpbPlSt yjM">', $res);
    // error_log($tmp[1]);
    
    $rc = preg_match_all('/title="(.+?)"/', $tmp[1] . $tmp[2], $matches);
    
    // error_log(print_r($matches, true));
    $list_team = $matches[1];
    
    $rc = preg_match_all('/<td>(.+?)</', $tmp[1] . $tmp[2], $matches);
    
    // error_log(print_r($matches, true));
    
    $min_value = 9999;
    for ($i = 0; $i < 12; $i++) {
        if ($min_value > (int)$matches[1][$i * 13 + 8]) {
            $min_value = (int)$matches[1][$i * 13 + 8];
        }
    }
    for ($i = 0; $i < 12; $i++) {
        $tmp1 = null;
        $tmp1->x = $matches[1][$i * 13 + 7];
        $tmp1->y = $matches[1][$i * 13 + 8] - $min_value;
        $tmp1->r = 10;
        $tmp2 = [];
        $tmp2[] = $tmp1;
        $tmp3 = null;
        $tmp3->label = $list_team[$i];
        $tmp3->data = $tmp2;
        $tmp3->backgroundColor = $color_index[$list_team[$i]];
        $datasets[] = $tmp3;
    }
    error_log(print_r($datasets, true));

    $data = ['type' => 'bubble',
             'data' => ['datasets' => $datasets],
            ];
    $url = 'https://quickchart.io/chart?width=675&height=360&c=' . json_encode($data);
    $res = $mu_->get_contents($url);
    
    header('Content-Type: image/png');
    echo $res;
    
    // error_log(print_r(json_decode('{"type":"scatter","data":{"datasets":[{"label":"A","data":[{"x":160,"y":116}]},{"label":"B","data":[{"x":171,"y":146}]}]}}'), true));
}
