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

    $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
    $url = 'https://' . $hatena_blog_id . '/search?q=upeemfeprvpub';
    $res = $mu_->get_contents($url);
    
    $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);
    
    $res = $mu_->get_contents($match[1]);
    $rc = preg_match('/<div class="upeemfeprvpub">(.+?)</', $res, $match);
    error_log(print_r(explode(' ', $match[1]), true));

    // $data2 = [550, 538, 526, 526, 504, 492, 480, 480, 444, 433, 423, 412, 402, 390, 390];
    foreach (explode(' ', $match[1]) as $item) {
        $data2[] = (int)($item / 60);
    }
    array_shift($data2);
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
    
    /*
    header('Content-Type: image/png');
    echo $res;
    return;
    */
    
    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    $im2 = imagecreatetruecolor(imagesx($im1) / 3, imagesy($im1) / 3);
    imagealphablending($im2, false);
    imagesavealpha($im2, true);
    imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 3, imagesy($im1) / 3, imagesx($im1), imagesy($im1));
    
    /*
    header('Content-Type: image/png');
    imagepng($im2, null, 9);
    return;
    */
    $file = tempnam("/tmp", md5(microtime(true)));
    imagepng($im2, $file, 9);
    imagedestroy($im2);
    $res = file_get_contents($file);
    unlink($file);
    
    $url = 'https://api.tinify.com/shrink';
    $options = [CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => 'api:' . getenv('TINYPNG_API_KEY'),
                CURLOPT_POST => true,
                CURLOPT_BINARYTRANSFER => true,
                CURLOPT_POSTFIELDS => $res,
                CURLOPT_HEADER => true,
               ];
    $res = $mu_->get_contents($url, $options);
    
    $tmp = preg_split('/^\r\n/m', $res, 2);
    
    $rc = preg_match('/compression-count: (.+)/i', $tmp[0], $match);
    error_log($log_prefix . 'Compression count : ' . $match[1]); // Limits 500/month
    $mu_->post_blog_wordpress('api.tinify.com', 'Compression count : ' . $match[1] . "\r\n" . 'Limits 500/month');
    $json = json_decode($tmp[1]);
    error_log($log_prefix . print_r($json, true));
    
    $url = $json->output->url;
    $options = [CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => 'api:' . getenv('TINYPNG_API_KEY'),
               ];
    $res = $mu_->get_contents($url, $options);
    $description = '<img src="data:image/png;base64,' . base64_encode($res) . '" />';
    $description = '<![CDATA[' . $description . ']]>';
    
    $mu_->post_blog_livedoor('TEST', $description);
    
    // header('Content-Type: image/png');
    // echo $res;
}
