<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190416($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190416($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    for ($i = 0; $i < (int)date('t'); $i++) {
        $labels[] = $i + 1;
        $data[] = ((int)date('t') - $i) * 24;
    }
    $data2 = [550, 538, 526, 526, 504, 492, 480, 480, 444, 433, 423, 412, 402, 390];
    $chart_data = ['type' => 'line',
                   'defaultFontSize' => 6,
                   'data' => ['labels' => $labels,
                              'datasets' => [['data' => $data,
                                              'fill' => false,
                                             ],
                                             ['data' => $data2,
                                              'fill' => false,
                                             ],
                                            ],
                             ],
                   'options' => ['legend' => ['display' => false,
                                             ],
                                 'animation' => ['duration' => 0,
                                                ],
                                 'hover' => ['animationDuration' => 0,
                                            ],
                                 'responsiveAnimationDuration' => 0,
                                 'plugins' => ['datalabels' => ['display' => true,
                                                               ],
                                              ],
                                ],
                  ];
    $url = 'https://quickchart.io/chart?width=600&height=320&c=' . json_encode($chart_data);
    $res = $mu_->get_contents($url);
    
    header('Content-Type: image/png');
    echo $res;
}
