<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_rss_items = tempnam('/tmp', 'rss_' . md5(microtime(true)));
@unlink($file_name_rss_items);

$rc = func_20190531($mu, $file_name_rss_items);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function func_20190531($mu_, $file_name_rss_items_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $keyword = 'ijesjwfvtbhf';
    
    $res = $mu_->search_blog($keyword);
    
    $data1 = [];
    $labels = [];
    foreach (explode(' ', $res) as $item) {
        $tmp1 = explode(',', $item);
        $tmp2 = new stdClass();
        $tmp2->x = (int)$tmp1[0];
        $tmp2->y = ceil((int)$tmp1[1] / 1024 / 1024);
        $data1[] = $tmp2;
        $labels[] = $tmp2->x;
    }

    $datasets[] = ['data' => $data1,
                   'fill' => false,
                   'pointStyle' => 'circle',
                   'backgroundColor' => 'black',
                   'borderColor' => 'black',
                   'borderWidth' => 1,
                   'pointRadius' => 2,
                   'pointBorderWidth' => 0,
                   'label' => 'hidrive',
                  ];
    
    $chart_data = ['type' => 'line',
                   'data' => ['labels' => $labels,
                              'datasets' => $datasets,
                             ],
                   'options' => ['legend' => ['labels' => ['usePointStyle' => true
                                                          ],
                                             ],
                                ],
                  ];
    
    $url = 'https://quickchart.io/chart?w=600&h=360&c=' . urlencode(json_encode($chart_data));
    $res = $mu_->get_contents($url);
    $url_length = strlen($url);
    
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
    
    return $url_length;
}
