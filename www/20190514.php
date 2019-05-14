<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190514($mu);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190514($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $cookie = tempnam('/tmp', md5(microtime(true)));

    $options = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
    ];

    $url = $mu_->get_env('URL_LOGGLY_USAGE');
    $res = $mu_->get_contents($url, $options);

    $rc = preg_match('/location: (.+)/i', $res, $match);

    $url = 'https://my.solarwinds.cloud/v1/login';

    $json = ['email' => $mu_->get_env('LOGGLY_ID', true),
             'loginQueryParams' => parse_url(trim($match[1]), PHP_URL_QUERY),
             'password' => $mu_->get_env('LOGGLY_PASSWORD', true),
            ];

    $options = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['content-type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($json),
    ];

    $res = $mu_->get_contents($url, $options);

    $url = json_decode($res)->redirectUrl;

    $options = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
    ];

    $res = $mu_->get_contents($url, $options);
    // error_log($log_prefix . print_r(json_decode($res)->total, true));

    foreach (json_decode($res)->total as $item) {
        error_log($log_prefix . date('m/d', $item[0] / 1000) . ' ' . round($item[1] / 1024 / 1024) . 'MB');
        $labels[] = date('m/d', $item[0] / 1000);
        $data[] = round($item[1] / 1024 / 1024);
    }
    array_pop($labels);
    array_pop($data);

    $data = ['type' => 'line',
             'data' => ['labels' => $labels,
                        'datasets' => [['data' => $data,
                                        'fill' => false,
                                        'borderColor' => 'black',
                                        'borderWidth' => 1,
                                        'pointBackgroundColor' => 'black',
                                        'pointRadius' => 2,
                                       ],
                                      ],
                       ],
             'options' => ['legend' => ['display' => false,],
                           'animation' => ['duration' => 0,],
                           'hover' => ['animationDuration' => 0,],
                           'responsiveAnimationDuration' => 0,
                           'scales' => $scales,
                           'annotation' => ['annotations' => [['type' => 'line',
                                                               'mode' => 'horizontal',
                                                               'scaleID' => 'y-axis-0',
                                                               'value' => 200,
                                                               'borderColor' => 'red',
                                                               'borderWidth' => 1,
                                                              ],
                                                             ],
                                           ],
                          ],
            ];

    $url = 'https://quickchart.io/chart?width=600&height=320&c=' . json_encode($data);
    $res = $mu_->get_contents($url);

    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    $im2 = imagecreatetruecolor(imagesx($im1) / 2, imagesy($im1) / 2);
    imagealphablending($im2, false);
    imagesavealpha($im2, true);
    imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 2, imagesy($im1) / 2, imagesx($im1), imagesy($im1));
    $file = tempnam('/tmp', md5(microtime(true)));
    imagepng($im2, $file, 9);
    imagedestroy($im2);
    $res = file_get_contents($file);
    unlink($file);

    header('Content-Type: image/png');
    echo $res;
}
