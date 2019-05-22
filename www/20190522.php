<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190522($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190522($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $sql = <<< __HEREDOC__
SELECT to_char(T1.check_time, 'YYYY/MM/DD') check_date
      ,MIN(T1.balance) balance
  FROM t_waon_history T1
 GROUP BY to_char(T1.check_time, 'YYYY/MM/DD')
 ORDER BY to_char(T1.check_time, 'YYYY/MM/DD') DESC
 LIMIT 20
;
__HEREDOC__;

    $pdo = $mu_->get_pdo();

    $labels = [];
    $data1 = [];
    foreach ($pdo->query($sql) as $row) {
        $labels[$row['check_date']] = date('m/d', strtotime($row['check_date']));
        $tmp = new stdClass();
        $tmp->x = date('m/d', strtotime($row['check_date']));
        $tmp->y = $row['balance'];
        $data1[] = $tmp;
    }
    $pdo = null;

    ksort($labels);
    $labels = array_values($labels);

    $datasets = [];

    $datasets[] = ['data' => $data1,
                   'fill' => false,
                   'pointStyle' => 'circle',
                   'backgroundColor' => 'deepskyblue',
                   'borderColor' => 'deepskyblue',
                   'borderWidth' => 3,
                   'pointRadius' => 4,
                   'pointBorderWidth' => 0,
                  ];

    $scales = new stdClass();
    $scales->yAxes[] = ['display' => true,
                        'ticks' => '__TICKS__',
                       ];

    $chart_data = ['type' => 'line',
                   'data' => ['labels' => $labels,
                              'datasets' => $datasets,
                             ],
                   'options' => ['legend' => ['display' => false,
                                             ],
                                 'animation' => ['duration' => 0,
                                                ],
                                 'hover' => ['animationDuration' => 0,
                                            ],
                                 'responsiveAnimationDuration' => 0,
                                 'annotation' => ['annotations' => [['type' => 'line',
                                                                     'mode' => 'horizontal',
                                                                     'scaleID' => 'y-axis-0',
                                                                     'value' => $data1[0]->y,
                                                                     'borderColor' => 'black',
                                                                     'borderWidth' => 1,
                                                                     'label' => ['enabled' => true,
                                                                                 'content' => number_format($data1[0]->y),
                                                                                ],
                                                                    ],
                                                                   ],
                                                 ],
                                 'scales' => $scales,
                                ],
                  ];

    $tmp = str_replace('"__TICKS__"', "{callback: function(value){return value.toLocaleString();}}", json_encode($chart_data));

    $url = 'https://quickchart.io/chart?width=600&height=360&c=' . urlencode($tmp);
    $res = $mu_->get_contents($url);

    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    $im2 = imagecreatetruecolor(imagesx($im1) / 2, imagesy($im1) / 2);
    imagealphablending($im2, false);
    imagesavealpha($im2, true);
    imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 2, imagesy($im1) / 2, imagesx($im1), imagesy($im1));
    imagedestroy($im1);

    $file = tempnam("/tmp", md5(microtime(true)));
    imagepng($im2, $file, 9);
    imagedestroy($im2);
    $res = file_get_contents($file);
    unlink($file);

    header('Content-Type: image/png');
    echo $res;
}
