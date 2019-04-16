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

    $data2 = [550, 538, 526, 526, 504, 492, 480, 480, 444, 433, 423, 412, 402, 390];
    $dy = ($data2[0] - end($data2)) / count($data2) + 1;
    for ($i = 0; $i < (int)date('t'); $i++) {
        $labels[] = $i + 1;
        $data[] = ((int)date('t') - $i) * 24;
        $data3[] = (int)($data2[0] - $dy * $i);
    }
    $chart_data = ['type' => 'line',
                   'data' => ['labels' => $labels,
                              'datasets' => [['data' => $data,
                                              'fill' => false,
                                              'borderColor' => 'black',
                                             ],
                                             ['data' => $data2,
                                              'fill' => false,
                                              'borderColor' => 'green',
                                             ],
                                             ['data' => $data3,
                                              'fill' => false,
                                              'pointStyle' => 'line',
                                              'borderColor' => 'red',
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
                                 /*
                                 'plugins' => ['datalabels' => ['display' => true,
                                                                'align' => 'bottom',
                                                               ],
                                              ],
                                 */
                                ],
                  ];
    $url = 'https://quickchart.io/chart?width=900&height=480&c=' . json_encode($chart_data);
    $res = $mu_->get_contents($url);
    
    /*
    header('Content-Type: image/png');
    echo $res;
    */
    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    $im2 = imagecreatetruecolor(imagesx($im1) / 3, imagesy($im1) / 3);
    imagealphablending($im2, false);
    imagesavealpha($im2, true);
    imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 3, imagesy($im1) / 3, imagesx($im1), imagesy($im1));
    header('Content-Type: image/png');
    imagepng($im2, null, 9);
    imagedestroy($im2);
}
