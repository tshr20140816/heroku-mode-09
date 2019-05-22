<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190522b($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190522b($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $livedoor_id = $mu_->get_env('LIVEDOOR_ID', true);
    $title = $mu_->get_env('TARGET_NAME_TITLE');
    $url = "http://blog.livedoor.jp/${livedoor_id}/search?q=" . str_replace(' ', '+', $title) . '+' . date('Y');
    $res = $mu_->get_contents($url);

    $rc = preg_match('/<div class="article-body-inner">(.+?)<\/div>/s', $res, $match);
    $base_record = trim(strip_tags($match[1]));
    error_log($log_prefix . $base_record);

    $name = $mu_->get_env('TARGET_NAME');
    $timestamp = strtotime('-13 hours');
    // $timestamp = mktime(0, 0, 0, 4, 21, 2019);

    if (strpos($base_record, date('Y/m/d', $timestamp)) != false) {
        return;
    }

    $ymd = date('Ymd', $timestamp);
    $url = 'https://baseball.yahoo.co.jp/npb/schedule/?date=' . $ymd;

    $res = $mu_->get_contents($url);

    $pattern = '<table border="0" cellspacing="0" cellpadding="0" class="teams">.+?';
    $pattern .= '<table border="0" cellspacing="0" cellpadding="0" class="score">.+?';
    $pattern .= '<a href="https:\/\/baseball.yahoo.co.jp\/npb\/game\/(\d+)\/".+?<\/table>.+?<\/table>';
    $rc = preg_match_all('/' . $pattern . '/s', $res, $matches, PREG_SET_ORDER);

    $url = '';
    foreach ($matches as $match) {
        if (strpos($match[0], '広島') != false) {
            $url = 'https://baseball.yahoo.co.jp/npb/game/' . $match[1] . '/stats';
            break;
        }
    }

    if ($url == '') {
        return;
    }
    $res = $mu_->get_contents($url);

    $description = '';
    foreach (explode('</table>', $res) as $data) {
        if (strpos($data, $name) != false) {
            $rc = preg_match_all('/<tr.*?>(.+?)<\/tr>/s', $data, $matches);
            foreach ($matches[1] as $item) {
                if (strpos($item, $name) != false) {
                    $tmp = str_replace("\n", '', $item);
                    $tmp = preg_replace('/<.+?>/s', ' ', $tmp);
                    $tmp = str_replace($name, '', $tmp);
                    $tmp = date('Y/m/d', $timestamp) . ' ' . trim(preg_replace('/ +/', ' ', $tmp));
                    $description = $tmp . "\n" . $base_record;
                    error_log($log_prefix . $description);
                    // $mu_->post_blog_wordpress_async($title, $description);
                    break 2;
                }
            }
        }
    }

    if ($description === '') {
        return;
    }

    $rc = preg_match_all('/(.+?) .+? (.+?) .+/', $description, $matches);
    $record_count = count($matches[0]);
    $labels = [];
    $data = [];
    $min_value = 1000;
    for ($i = 0; $i < $record_count; $i++) {
        error_log($log_prefix . $matches[1][$record_count - $i - 1] . ' ' . $matches[2][$record_count - $i - 1]);
        $labels[] = substr($matches[1][$record_count - $i - 1], 5);
        $data[] = $matches[2][$record_count - $i - 1] * 1000;
        if ($min_value > $data[$i]) {
            $min_value = $data[$i];
        }
    }

    $scales->yAxes[] = ['display' => true,
                        'bottom' => $min_value,
                       ];

    $data = ['type' => 'line',
             'data' => ['labels' => $labels,
                        'datasets' => [['data' => $data,
                                        'fill' => false,
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
                                                               'mode' => 'horizontal',
                                                               'scaleID' => 'y-axis-0',
                                                               'value' => end($data),
                                                               'borderColor' => 'rgba(0,0,0,0)',
                                                               'borderWidth' => 1,
                                                               'label' => ['enabled' => true,
                                                                           'content' => end($data),
                                                                           'position' => 'left',
                                                                          ],
                                                              ],
                                                             ],
                                           ],
                           'scales' => $scales,
                          ],
            ];
    $url = 'https://quickchart.io/chart?width=600&height=320&c=' . urlencode(json_encode($data));
    $res = $mu_->get_contents($url);

    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    if (imagesx($im1) !== 600) {
        $im2 = imagecreatetruecolor(imagesx($im1) / 2, imagesy($im1) / 2);
        imagealphablending($im2, false);
        imagesavealpha($im2, true);
        imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 2, imagesy($im1) / 2, imagesx($im1), imagesy($im1));
        @unlink('/tmp/average.png');
        imagepng($im2, '/tmp/average.png', 9);
        imagedestroy($im2);
        $res = file_get_contents('/tmp/average.png');
    }
    imagedestroy($im1);

    header('Content-Type: image/png');
    echo $res;
}

function func_20190522($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $sql = <<< __HEREDOC__
SELECT to_char(T1.check_time, 'YYYY/MM/DD') check_date
      ,MIN(T1.balance) balance
  FROM t_waon_history T1
 GROUP BY to_char(T1.check_time, 'YYYY/MM/DD')
 ORDER BY to_char(T1.check_time, 'YYYY/MM/DD') DESC
 LIMIT 20
;
__HEREDOC__;

    $pdo = $mu_->get_pdo();

    $labels = [];
    $data1 = [];
    foreach ($pdo->query($sql) as $row) {
        $labels[$row['check_date']] = date('m/d', strtotime($row['check_date']));
        $tmp = new stdClass();
        $tmp->x = date('m/d', strtotime($row['check_date']));
        $tmp->y = $row['balance'];
        $data1[] = $tmp;
    }
    $pdo = null;

    ksort($labels);
    $labels = array_values($labels);

    $datasets = [];

    $datasets[] = ['data' => $data1,
                   'fill' => false,
                   'pointStyle' => 'circle',
                   'backgroundColor' => 'deepskyblue',
                   'borderColor' => 'deepskyblue',
                   'borderWidth' => 3,
                   'pointRadius' => 4,
                   'pointBorderWidth' => 0,
                  ];

    $scales = new stdClass();
    $scales->yAxes[] = ['display' => true,
                        'ticks' => '__TICKS__',
                       ];

    $chart_data = ['type' => 'line',
                   'data' => ['labels' => $labels,
                              'datasets' => $datasets,
                             ],
                   'options' => ['legend' => ['display' => false,
                                             ],
                                 'animation' => ['duration' => 0,
                                                ],
                                 'hover' => ['animationDuration' => 0,
                                            ],
                                 'responsiveAnimationDuration' => 0,
                                 'annotation' => ['annotations' => [['type' => 'line',
                                                                     'mode' => 'horizontal',
                                                                     'scaleID' => 'y-axis-0',
                                                                     'value' => $data1[0]->y,
                                                                     'borderColor' => 'black',
                                                                     'borderWidth' => 1,
                                                                     'label' => ['enabled' => true,
                                                                                 'content' => number_format($data1[0]->y),
                                                                                ],
                                                                    ],
                                                                   ],
                                                 ],
                                 'scales' => $scales,
                                ],
                  ];

    $tmp = str_replace('"__TICKS__"', "{callback: function(value){return value.toLocaleString();}}", json_encode($chart_data));

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
