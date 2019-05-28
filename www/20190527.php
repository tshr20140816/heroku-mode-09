<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_rss_items = tempnam('/tmp', 'rss_' . md5(microtime(true)));
@unlink($file_name_rss_items);

func_20190527b($mu, $file_name_rss_items);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190527b($mu_, $file_name_rss_items_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = 'https://' . $mu_->get_env('WORDPRESS_USERNAME', true) . '.wordpress.com/?s=daily020.php';
    
    $res = $mu_->get_contents($url);
    
    // error_log($res);
    
    $rc = preg_match_all('/rel="bookmark">(.+?) .+? \/daily020\.php&nbsp;\[(.+?)s\]/', $res, $maches);
    error_log(print_r($maches, true));
}

function func_20190527($mu_, $file_name_rss_items_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    for ($i = 0; $i < (int)date('t'); $i++) {
        $labels[] = $i + 1;
    }

    $datasets = [];

    $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
    $list = [['target' => 'toodledo',
              'color' => 'green',
              'size_color' => 'red',
             ],
             ['target' => 'ttrss',
              'color' => 'deepskyblue',
              'size_color' => 'orange',
             ],
             ['target' => 'redmine',
              'color' => 'blue',
              'size_color' => 'yellow',
             ],
            ];

    $annotations = [];
    $level = 10000;
    foreach ($list as $one_data) {
        error_log(print_r($one_data, true));
        $keyword = strtolower($one_data['target']);
        for ($i = 0; $i < strlen($keyword); $i++) {
            $keyword[$i] = chr(ord($keyword[$i]) + 1);
        }

        $url = 'https://' . $hatena_blog_id . '/search?q=' . $keyword . 'sfdpsedpvou';
        $res = $mu_->get_contents($url);

        $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);

        $res = $mu_->get_contents($match[1]);
        $rc = preg_match('/<div class="' . $keyword . 'sfdpsedpvou">(.+?)</', $res, $match);

        $data2 = [];
        foreach (explode(' ', $match[1]) as $item) {
            $tmp1 = explode(',', $item);
            $tmp2 = new stdClass();
            $tmp2->x = (int)$tmp1[0];
            $tmp2->y = (int)$tmp1[1];
            $data2[] = $tmp2;
        }

        if (count($data2) < 2) {
            return;
        }

        $level -= 1000;
        $annotations[] = ['type' => 'line',
                          'mode' => 'horizontal',
                          'scaleID' => 'y-axis-0',
                          'value' => $level,
                          'borderColor' => 'rgba(0,0,0,0)',
                          'borderWidth' => 1,
                          'label' => ['enabled' => true,
                                      'content' => number_format(end($data2)->y),
                                      'position' => 'left',
                                      'backgroundColor' => $one_data['color'],
                                     ],
                         ];
        
        $datasets[] = ['data' => $data2,
                       'fill' => false,
                       'pointStyle' => 'circle',
                       'backgroundColor' => $one_data['color'],
                       'borderColor' => $one_data['color'],
                       'borderWidth' => 3,
                       'pointRadius' => 4,
                       'pointBorderWidth' => 0,
                       'label' => $one_data['target'] . ' record',
                       'yAxisID' => 'y-axis-0',
                      ];

        $url = 'https://' . $hatena_blog_id . '/search?q=' . $keyword . 'ebubcbtftjaf';
        $res = $mu_->get_contents($url);

        $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);

        $res = $mu_->get_contents($match[1]);
        $rc = preg_match('/<div class="' . $keyword . 'ebubcbtftjaf">(.+?)</', $res, $match);

        $data3 = [];
        foreach (explode(' ', $match[1]) as $item) {
            $tmp1 = explode(',', $item);
            $tmp2 = new stdClass();
            $tmp2->x = (int)$tmp1[0];
            $tmp2->y = ceil((int)$tmp1[1] / 1024 / 1024);
            $data3[] = $tmp2;
        }

        $annotations[] = ['type' => 'line',
                          'mode' => 'horizontal',
                          'scaleID' => 'y-axis-0',
                          'value' => $level,
                          'borderColor' => 'rgba(0,0,0,0)',
                          'borderWidth' => 1,
                          'label' => ['enabled' => true,
                                      'content' => number_format(end($data3)->y),
                                      'position' => 'right',
                                      'backgroundColor' => $one_data['size_color'],
                                      'fontColor' => 'black',
                                     ],
                         ];

        $datasets[] = ['data' => $data3,
                       'fill' => false,
                       'pointStyle' => 'star',
                       'backgroundColor' => $one_data['size_color'],
                       'borderColor' => $one_data['size_color'],
                       'borderWidth' => 2,
                       'pointRadius' => 3,
                       'pointBorderWidth' => 0,
                       'label' => 'size',
                       'yAxisID' => 'y-axis-1',
                      ];
    }

    $scales = new stdClass();
    $scales->yAxes[] = ['id' => 'y-axis-0',
                        'display' => true,
                        'position' => 'left',
                        'type' => 'linear',
                        'ticks' => ['callback' => '__TICKS1__',],
                       ];
    $scales->yAxes[] = ['id' => 'y-axis-1',
                        'display' => true,
                        'position' => 'right',
                        'type' => 'linear',
                        'ticks' => ['callback' => '__TICKS2__',],
                       ];

    $annotations[] = ['type' => 'line',
                      'mode' => 'horizontal',
                      'scaleID' => 'y-axis-0',
                      'value' => 0,
                      'borderColor' => 'rgba(0,0,0,0)',
                      'borderWidth' => 1,
                     ];
    $annotations[] = ['type' => 'line',
                      'mode' => 'horizontal',
                      'scaleID' => 'y-axis-0',
                      'value' => 10000,
                      'borderColor' => 'red',
                      'borderWidth' => 1,
                     ];
    $annotations[] = ['type' => 'line',
                      'mode' => 'horizontal',
                      'scaleID' => 'y-axis-1',
                      'value' => 0,
                      'borderColor' => 'rgba(0,0,0,0)',
                      'borderWidth' => 1,
                     ];
    $annotations[] = ['type' => 'line',
                      'mode' => 'horizontal',
                      'scaleID' => 'y-axis-1',
                      'value' => 1000,
                      'borderColor' => 'rgba(0,0,0,0)',
                      'borderWidth' => 1,
                     ];
    $chart_data = ['type' => 'line',
                   'data' => ['labels' => $labels,
                              'datasets' => $datasets,
                             ],
                   'options' => ['legend' => ['display' => true,
                                              'labels' => ['usePointStyle' => true
                                                          ],
                                             ],
                                 'animation' => ['duration' => 0,
                                                ],
                                 'hover' => ['animationDuration' => 0,
                                            ],
                                 'responsiveAnimationDuration' => 0,
                                 'scales' => $scales,
                                 'annotation' => ['annotations' => $annotations,
                                                 ],
                                ],
                  ];

    $tmp = str_replace('"__TICKS1__"', "function(value){return value.toLocaleString();}", json_encode($chart_data));
    $tmp = str_replace('"__TICKS2__"', "function(value){return value.toLocaleString() + 'MB';}", $tmp);

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
