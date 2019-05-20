<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190520($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190520b($mu_)
{
    $json = ['long_url' => 'https://github.com/'];
    $url = 'https://api-ssl.bitly.com/v4/bitlinks';
    $acess_token = getenv('BITLY_ACCESS_TOKEN');
    $options = [CURLOPT_HTTPHEADER => ["Authorization: Bearer ${acess_token}",
                                       'Content-Type: application/json',
                                      ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($json),
                CURLOPT_HEADER => true,
               ];
    $res = $mu_->get_contents($url, $options);
    error_log($res);
}

function func_20190520($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    for ($i = 0; $i < (int)date('t'); $i++) {
        $labels[] = $i + 1;
        $data1[] = ((int)date('t') - $i) * 24;
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
    foreach (['toodledo', 'ttrss'] as $target) {
    // foreach (['toodledo',] as $target) {
        $keyword = strtolower($target);
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
            $data2[] = (int)($item / 60);
        }
        
        if (count($data2) < 3) {
            return;
        }
        array_shift($data2);
        $data2[0] = 550;

        $datasets[] = ['data' => $data2,
                       'fill' => false,
                       'pointStyle' => 'circle',
                       // 'borderColor' => 'green',
                       'borderWidth' => 3,
                       'pointRadius' => 4,
                       'label' => $target,
                      ];
        
        $data3 = [];
        $dy = ($data2[0] - end($data2)) / count($data2) + 1;
        for ($i = 0; $i < (int)date('t'); $i++) {
            $data3[] = (int)($data2[0] - $dy * $i);
        }
        $datasets[] = ['data' => $data3,
                       'fill' => false,
                       'backgroundColor' => 'red',
                       'borderWidth' => 1,
                       'borderColor' => 'red',
                       'pointRadius' => 0,
                       'label' => $target . ' plan',
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
                                                                     'value' => 19,
                                                                    ],
                                                                   ],
                                                 ],
                                ],
                  ];
    $url = 'https://quickchart.io/chart?width=900&height=480&c=' . json_encode($chart_data);
    // $res = $mu_->get_contents($url, [CURLOPT_HTTPHEADER => ['Expect: 100-continue']]);
    // $url = 'https://bit.ly/2HAUNum';
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

function make_heroku_usage_graph($mu_, $file_name_rss_items_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $target_ = 'TOODLEDO';
    $keyword = strtolower($target_);
    for ($i = 0; $i < strlen($keyword); $i++) {
        $keyword[$i] = chr(ord($keyword[$i]) + 1);
    }

    $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
    $url = 'https://' . $hatena_blog_id . '/search?q=' . $keyword . 'rvpub';
    $res = $mu_->get_contents($url);

    $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);

    $res = $mu_->get_contents($match[1]);
    $rc = preg_match('/<div class="' . $keyword . 'rvpub">(.+?)</', $res, $match);

    $data2 = [];
    foreach (explode(' ', $match[1]) as $item) {
        $data2[] = (int)($item / 60);
    }
    if (count($data2) < 3) {
        return;
    }
    array_shift($data2);
    $data2[0] = 550;
    $dy = ($data2[0] - end($data2)) / count($data2) + 1;
    for ($i = 0; $i < (int)date('t'); $i++) {
        $labels[] = $i + 1;
        $data1[] = ((int)date('t') - $i) * 24;
        $data3[] = (int)($data2[0] - $dy * $i);
    }
    $chart_data = ['type' => 'line',
                   'data' => ['labels' => $labels,
                              'datasets' => [['data' => $data1,
                                              'fill' => false,
                                              'pointStyle' => 'line',
                                              'borderColor' => 'black',
                                             ],
                                             ['data' => $data2,
                                              'fill' => false,
                                              'pointStyle' => 'cross',
                                              'borderColor' => 'green',
                                              'borderWidth' => 5,
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
                                 'annotation' => ['annotations' => [['type' => 'line',
                                                                     'mode' => 'vertical',
                                                                     'scaleID' => 'x-axis-0',
                                                                     'value' => count($data2),
                                                                    ],
                                                                   ],
                                                 ],
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

    header('Content-Type: image/php');
    echo $res;
}
