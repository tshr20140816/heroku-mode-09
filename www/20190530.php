<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_rss_items = tempnam('/tmp', 'rss_' . md5(microtime(true)));
@unlink($file_name_rss_items);

$rc = func_20190530($mu, $file_name_rss_items);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function func_20190530($mu_, $file_name_rss_items_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $res = $mu_->get_contents('https://github.com/tshr20140816');
    
    $rc = preg_match_all('/<rect class="day" .+?data-count="(.+?)".*?data-date="(.+?)"/', $res, $matches, PREG_SET_ORDER);
    
    error_log(print_r($matches, true));
    
    $labels = [];
    $data1 = [];
    $data2 = [];
    $data3 = [];
    foreach (array_slice($matches, -28) as $match) {
        if (date('w', strtotime($match[2])) == '0') {
            $tmp = new stdClass();
            $tmp->x = substr($match[2], -2);
            $tmp;->y = 0;
            $data2[] = $tmp;
        } else if (date('w', strtotime($match[2])) == '6') {
            $tmp = new stdClass();
            $tmp->x = substr($match[2], -2);
            $tmp;->y = 0;
            $data3[] = $tmp;
        }
        $tmp = new stdClass();
        $tmp->x = substr($match[2], -2);
        $tmp->y = (int)$match[1];
        $data1[] = $tmp;
        $labels[] = substr($match[2], -2);
    }

    $data = ['type' => 'line',
             'data' => ['labels' => $labels,
                        'datasets' => [['data' => $data1,
                                        'fill' => false,
                                        'borderColor' => 'black',
                                        'borderWidth' => 1,
                                        'pointBackgroundColor' => 'black',
                                        'pointRadius' => 2,
                                       ],
                                       ['data' => $data2,
                                        'fill' => false,
                                        'showLine' => false,
                                        'pointBackgroundColor' => 'red',
                                        'pointRadius' => 2,
                                       ],
                                       ['data' => $data3,
                                        'fill' => false,
                                        'showLine' => false,
                                        'pointBackgroundColor' => 'blue',
                                        'pointRadius' => 2,
                                       ],
                                      ],
                       ],
             'options' => ['legend' => ['display' => false,],
                           'animation' => ['duration' => 0,],
                           'hover' => ['animationDuration' => 0,],
                           'responsiveAnimationDuration' => 0,
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
