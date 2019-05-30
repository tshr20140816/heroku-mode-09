<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_rss_items = tempnam('/tmp', 'rss_' . md5(microtime(true)));
@unlink($file_name_rss_items);

$rc = func_20190529($mu, $file_name_rss_items);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function func_20190529($mu_, $file_name_rss_items_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $sql = <<< __HEREDOC__
SELECT T1.yyyymmdd
      ,T1.post_count
  FROM t_blog_post T1
 WHERE T1.blog_site = 'hatena'
 ORDER BY T1.yyyymmdd DESC
 LIMIT 25
;
__HEREDOC__;
    
    $pdo = $mu_->get_pdo();
    
    $labels = [];
    $data1 = [];
    $data2 = [];
    foreach ($pdo->query($sql) as $row) {
        $labels[$row['yyyymmdd']] = substr($row['yyyymmdd'], -2);
        $tmp = new stdClass();
        $tmp->x = substr($row['yyyymmdd'], -2);
        $tmp->y = (int)$row['post_count'];
        $data1[] = $tmp;
        if (count($data2) == 0) {
            $data2[] = $tmp;
        } else {
            if ($data2[0]->y < $tmp->y) {
                $data2[0] = $tmp;
            }
        }
    }
    $pdo = null;
    
    ksort($labels);
    $labels = array_values($labels);
    
    $scales = new stdClass();
    $scales->yAxes[] = ['id' => 'y-axis-0',
                        'display' => true,
                        'position' => 'left',
                        'ticks' => ['beginAtZero' => true,
                                    'max' => 100,
                                   ],
                       ];
    $scales->yAxes[] = ['id' => 'y-axis-1',
                        'display' => true,
                        'position' => 'right',
                        'ticks' => ['beginAtZero' => true,
                                    'max' => 100,
                                   ],
                       ];
    
    $data = ['type' => 'line',
             'data' => ['labels' => $labels,
                        'datasets' => [['data' => $data1,
                                        'fill' => false,
                                        'borderColor' => 'black',
                                        'borderWidth' => 1,
                                        'pointBackgroundColor' => 'black',
                                        'pointRadius' => 2,
                                        'yAxisID' => 'y-axis-0',
                                       ],
                                       /*
                                       ['data' => $data2,
                                        'fill' => false,
                                        'pointRadius' => 1,
                                        'yAxisID' => 'y-axis-1',
                                       ],
                                       */
                                      ],
                       ],
             'options' => ['legend' => ['display' => false,],
                           'animation' => ['duration' => 0,],
                           'hover' => ['animationDuration' => 0,],
                           'responsiveAnimationDuration' => 0,
                           'scales' => $scales,
                          ],
            ];

    $url = 'https://quickchart.io/chart?width=600&height=320&c=' . urlencode(json_encode($data));
    $res = $mu_->get_contents($url);
    $url_length = strlen($url);

    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    $im2 = imagecreatetruecolor(imagesx($im1) / 2, imagesy($im1) / 2);
    imagealphablending($im2, false);
    imagesavealpha($im2, true);
    imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 2, imagesy($im1) / 2, imagesx($im1), imagesy($im1));
    imagedestroy($im1);
    $file = tempnam('/tmp', 'png_' . md5(microtime(true)));
    imagepng($im2, $file, 9);
    imagedestroy($im2);
    $res = file_get_contents($file);
    unlink($file);
    
    header('Content-Type: image/png');
    echo $res;

    return $url_length;
}
