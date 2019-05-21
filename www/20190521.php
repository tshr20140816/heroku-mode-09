<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190521b($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');


function func_20190521b($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $url = $mu_->get_env('URL_TTRSS_1');
    
    $tmp = parse_url($url);
    
    error_log(print_r($tmp, true));
    
    return;
    $options = [CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => getenv('TEST_USER') . ':' . getenv('TEST_PASSWORD'),
               ];
    $res = $mu_->get_contents($url, $options);
    error_log($res);
}

function func_20190521($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    for ($i = 0; $i < (int)date('t'); $i++) {
        $labels[] = $i + 1;
    }

    $datasets = [];

    $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
    $list = [['target' => 'toodledo',
              'color' => 'green',
             ],
            ];
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
            // $data2[] = (int)($item / 60);
            $tmp1 = explode(',', $item);
            $tmp2 = new stdClass();
            $tmp2->x = (int)$tmp1[0];
            $tmp2->y = (int)$tmp1[1];
            $data2[] = $tmp2;
        }
        
        if (count($data2) < 3) {
            return;
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
