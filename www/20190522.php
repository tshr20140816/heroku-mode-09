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
 LIMIT 40
;
__HEREDOC__;
    
    $pdo = $mu_->get_pdo();
    
    $labels = [];
    $data1 = [];
    foreach ($pdo->query($sql) as $row) {
        error_log(print_r($row, true));
        error_log(date('m/d', strtotime($row['check_date'])));
        $labels[$row['check_date']] = date('m/d', strtotime($row['check_date']));
        $tmp = new stdClass();
        $tmp->x = date('m/d', strtotime($row['check_date']));
        $tmp->y = $row['balance'];
        $data1[] = $tmp;
    }
    $pdo = null;
    
    ksort($labels);
    $labels = array_values($labels);
    
    error_log(print_r($labels, true));
    
    $datasets = [];
    
    $datasets[] = ['data' => $data1,
                   'fill' => false,
                   'pointStyle' => 'circle',
                   'backgroundColor' => 'deepskyblue',
                   'borderColor' => 'deepskyblue',
                   'borderWidth' => 3,
                   'pointRadius' => 4,
                   'pointBorderWidth' => 0,
                   'label' => 'waon',
                  ];
    
    $chart_data = ['type' => 'line',
                   'data' => ['labels' => $labels,
                              'datasets' => $datasets,
                             ],
                   'options' => ['legend' => ['display' => true,
                                             ],
                                 'animation' => ['duration' => 0,
                                                ],
                                 'hover' => ['animationDuration' => 0,
                                            ],
                                 'responsiveAnimationDuration' => 0,
                                ],
                  ];
    $url = 'https://quickchart.io/chart?width=900&height=480&c=' . urlencode(json_encode($chart_data));
    $res = $mu_->get_contents($url);
    
    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    $im2 = imagecreatetruecolor(imagesx($im1) / 3, imagesy($im1) / 3);
    imagealphablending($im2, false);
    imagesavealpha($im2, true);
    imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 3, imagesy($im1) / 3, imagesx($im1), imagesy($im1));
    imagedestroy($im1);
    
    $file = tempnam("/tmp", md5(microtime(true)));
    imagepng($im2, $file, 9);
    imagedestroy($im2);
    $res = file_get_contents($file);
    unlink($file);
    
    header('Content-Type: image/png');
    echo $res;
}
