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

    for ($i = 0; $i < (int)date('t'); $i++) {
        $labels[] = $i + 1;
        // $data1[] = ((int)date('t') - $i) * 24;
        $tmp = new stdClass();
        $tmp->x = $i + 1;
        $tmp->y = ((int)date('t') - $i) * 24;
        $data1[] = $tmp;
    }

    $datasets = [];
    $datasets[] = ['data' => $data1,
                   'fill' => false,
                   'pointStyle' => 'line',
                   'backgroundColor' => 'black',
                   'borderColor' => 'black',
                   'borderWidth' => 1,
                   'pointRadius' => 0,
                   'label' => 'max',
                  ];

    $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
    $list = [['target' => 'toodledo',
              'color' => 'green',
              'planColor' => 'red',
             ],
             ['target' => 'ttrss',
              'color' => 'cyan',
              'planColor' => 'orange',
             ],
            ];
    foreach ($list as $one_data) {
        error_log(print_r($one_data, true));
        $keyword = strtolower($one_data['target']);
        for ($i = 0; $i < strlen($keyword); $i++) {
            $keyword[$i] = chr(ord($keyword[$i]) + 1);
        }

        $url = 'https://' . $hatena_blog_id . '/search?q=' . $keyword . 'rvpub';
        $res = $mu_->get_contents($url);

        $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);

        $res = $mu_->get_contents($match[1]);
        $rc = preg_match('/<div class="' . $keyword . 'rvpub">(.+?)</', $res, $match);

        $data2 = [];
        foreach (explode(' ', $match[1]) as $item) {
            // $data2[] = (int)($item / 60);
            $tmp1 = explode(',', $item);
            $tmp2 = new stdClass();
            $tmp2->x = (int)$tmp1[0] - 1;
            $tmp2->y = (int)((int)$tmp1[1] / 60);
            $data2[] = $tmp2;
        }

        if (count($data2) < 3) {
            return;
        }
        if ($data2[0]->x == 0) {
            array_shift($data2);
            // $data2[0] = 550;
            $tmp = new stdClass();
            $tmp->x = 1;
            $tmp->y = 550;
            $data2[0] = $tmp;
        }

        $datasets[] = ['data' => $data2,
                       'fill' => false,
                       'pointStyle' => 'circle',
                       'backgroundColor' => $one_data['color'],
                       'borderColor' => $one_data['color'],
                       'borderWidth' => 3,
                       'pointRadius' => 4,
                       'pointBorderWidth' => 0,
                       'label' => $one_data['target'],
                      ];

        $data3 = [];
        /*
        $dy = ($data2[0] - end($data2)) / count($data2) + 1;
        for ($i = 0; $i < (int)date('t'); $i++) {
            $data3[] = (int)($data2[0] - $dy * $i);
        }
        */
        $tmp = new stdClass();
        $tmp->x = 1;
        $tmp->y = 550;
        $data3[] = $tmp;
        $tmp = new stdClass();
        $tmp->x = (int)date('t');
        $tmp->y = 550 - (int)((550 - end($data2)->y) / end($data2)->x + 1) * (int)date('t');
        $data3[] = $tmp;
        
        error_log(print_r($data3, true));
        $datasets[] = ['data' => $data3,
                       'fill' => false,
                       'backgroundColor' => $one_data['planColor'],
                       'borderWidth' => 3,
                       'borderColor' => $one_data['planColor'],
                       'pointRadius' => 0,
                       'label' => $one_data['target'] . ' plan',
                      ];

    }
    
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
                                 'annotation' => ['annotations' => [['type' => 'line',
                                                                     'mode' => 'vertical',
                                                                     'scaleID' => 'x-axis-0',
                                                                     'value' => count($datasets[1]['data']),
                                                                    ],
                                                                   ],
                                                 ],
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
