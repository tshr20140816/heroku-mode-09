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

    $color_index['広島'] = 'red,red';
    $color_index['ヤクルト'] = 'cyan,yellowgreen';
    $color_index['巨人'] = 'black,orange';
    $color_index['ＤｅＮＡ'] = 'blue,blue';
    $color_index['中日'] = 'dodgerblue,dodgerblue';
    $color_index['阪神'] = 'yellow,yellow';
    $color_index['西武'] = 'navy,navy';
    $color_index['ソフトバンク'] = 'gold,black';
    $color_index['日本ハム'] = 'darkgray,steelblue';
    $color_index['オリックス'] = 'sandybrown,darkslategray';
    $color_index['ロッテ'] = 'black,silver';
    $color_index['楽天'] = 'darkred,orange';
    
    $url = 'https://baseball.yahoo.co.jp/npb/standings/';
    $res = $mu_->get_contents($url);
    
    $tmp = explode('<table class="NpbPlSt yjM">', $res);
    // error_log($tmp[1]);
    
    $rc = preg_match_all('/title="(.+?)"/', $tmp[1] . $tmp[2], $matches);
    
    // error_log(print_r($matches, true));
    $list_team = $matches[1];
    
    $rc = preg_match_all('/<td>(.+?)</', $tmp[1] . $tmp[2], $matches);
    
    // error_log(print_r($matches, true));
    
    $loss_sum = 0;
    $gain_sum = 0;
    $loss_min_value = 9999;
    for ($i = 0; $i < 12; $i++) {
        $gain_sum += (int)$matches[1][$i * 13 + 7];
        $loss_sum += (int)$matches[1][$i * 13 + 8];
        if ($loss_min_value > (int)$matches[1][$i * 13 + 8]) {
            $loss_min_value = (int)$matches[1][$i * 13 + 8];
        }
    }
    $loss_avg = round($loss_sum / 12);
    $gain_avg = round($gain_sum / 12);
    for ($i = 0; $i < 12; $i++) {
        $tmp1 = null;
        $tmp1->x = $matches[1][$i * 13 + 7];
        $tmp1->y = $matches[1][$i * 13 + 8] - $loss_min_value;
        $tmp1->r = 7;
        $tmp2 = [];
        $tmp2[] = $tmp1;
        $tmp3 = null;
        $tmp3->label = $list_team[$i];
        $tmp3->data = $tmp2;
        $tmp3->backgroundColor = explode(',', $color_index[$list_team[$i]])[0];
        $tmp3->borderWidth = 3;
        $tmp3->borderColor = explode(',', $color_index[$list_team[$i]])[1];
        $datasets[] = $tmp3;
    }
    error_log(print_r($datasets, true));

    $scales = null;
    $scales->xAxes[] = ['display' => false,];
    $scales->yAxes[] = ['display' => false,];
    $data = ['type' => 'bubble',
             'data' => ['datasets' => $datasets],
             'options' => ['legend' => ['position' => 'bottom',
                                        'labels' => ['fontSize' => 10,],
                                       ],
                           'scales' => $scales,
                           'annotation' => ['annotations' => [['type' => 'line',
                                                               'mode' => 'vertical',
                                                               'scaleID' => 'x-axis-0',
                                                               'value' => $gain_avg,
                                                               'borderColor' => 'black',
                                                               'borderWidth' => 1,
                                                               'label' => ['content' => 'TEST_A',],
                                                              ],
                                                              ['type' => 'line',
                                                               'mode' => 'horizontal',
                                                               'scaleID' => 'y-axis-0',
                                                               'value' => $loss_avg - $loss_min_value,
                                                               'borderColor' => 'black',
                                                               'borderWidth' => 1,
                                                               'label' => ['content' => 'TEST_B',],
                                                              ],
                                                             ],
                                           ],
                          ],
            ];
    $url = 'https://quickchart.io/chart?width=600&height=320&c=' . json_encode($data);
    $res = $mu_->get_contents($url);
    
    header('Content-Type: image/png');
    echo $res;
}
