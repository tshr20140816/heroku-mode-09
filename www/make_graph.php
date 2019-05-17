<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_rss_items = tempnam('/tmp', md5(microtime(true)));
@unlink($file_name_rss_items);

make_score_map($mu, $file_name_rss_items);
make_usage_graph($mu, $file_name_rss_items, 'TOODLEDO');
make_usage_graph($mu, $file_name_rss_items, 'TTRSS');
make_loggly_usage($mu, $file_name_rss_items);

$xml_text = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
<title>Graph</title>
<link>http://dummy.local/</link>
<description>Graph</description>
__ITEMS__
</channel>
</rss>
__HEREDOC__;

$file = '/tmp/' . getenv('FC2_RSS_03') . '.xml';
file_put_contents($file, str_replace('__ITEMS__', file_get_contents($file_name_rss_items), $xml_text));
$filesize = filesize($file);
$mu->upload_fc2($file);
unlink($file);
unlink($file_name_rss_items);

$time_finish = microtime(true);
$mu->post_blog_wordpress("${requesturi} [" . substr(($time_finish - $time_start), 0, 6) . 's]',
                         'file size : ' . number_format($filesize) . 'byte');

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

function make_score_map($mu_, $file_name_rss_items_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $color_index['広島'] = 'red,red';
    $color_index['ヤクルト'] = 'cyan,yellowgreen';
    $color_index['巨人'] = 'black,orange';
    $color_index['ＤｅＮＡ'] = 'blue,blue';
    $color_index['中日'] = 'dodgerblue,dodgerblue';
    $color_index['阪神'] = 'yellow,yellow';
    $color_index['西武'] = 'navy,navy';
    $color_index['ソフトバンク'] = 'gold,black';
    $color_index['日本ハム'] = 'darkgray,steelblue';
    $color_index['オリックス'] = 'sandybrown,darkslategray';
    $color_index['ロッテ'] = 'black,silver';
    $color_index['楽天'] = 'darkred,orange';

    $url = 'https://baseball.yahoo.co.jp/npb/standings/';
    $res = $mu_->get_contents($url);

    $tmp = explode('<table class="NpbPlSt yjM">', $res);

    $rc = preg_match_all('/title="(.+?)"/', $tmp[1] . $tmp[2], $matches);

    $list_team = $matches[1];

    $rc = preg_match_all('/<td>(.+?)</', $tmp[1] . $tmp[2], $matches);

    $gain_sum = 0;
    $gain_min_value = 9999;
    $gain_max_value = 0;
    $loss_sum = 0;
    $loss_min_value = 9999;
    $loss_max_value = 0;
    for ($i = 0; $i < 12; $i++) {
        $gain = (int)$matches[1][$i * 13 + 7];
        $loss = (int)$matches[1][$i * 13 + 8];

        $gain_sum += $gain;
        if ($gain_max_value < $gain) {
            $gain_max_value = $gain;
        }
        if ($gain_min_value > $gain) {
            $gain_min_value = $gain;
        }

        $loss_sum += $loss;
        if ($loss_max_value < $loss) {
            $loss_max_value = $loss;
        }
        if ($loss_min_value > $loss) {
            $loss_min_value = $loss;
        }
    }
    $loss_avg = round($loss_sum / 12);
    $gain_avg = round($gain_sum / 12);
    for ($i = 0; $i < 12; $i++) {
        $tmp1 = new stdClass();
        $tmp1->x = $matches[1][$i * 13 + 7];
        $tmp1->y = $matches[1][$i * 13 + 8];
        $tmp1->r = 7;
        $tmp2 = [];
        $tmp2[] = $tmp1;
        $tmp3 = new stdClass();
        $tmp3->label = $list_team[$i];
        $tmp3->data = $tmp2;
        $tmp3->backgroundColor = explode(',', $color_index[$list_team[$i]])[0];
        $tmp3->borderWidth = 3;
        $tmp3->borderColor = explode(',', $color_index[$list_team[$i]])[1];
        $datasets[] = $tmp3;
    }
    
    $data2 = [];
    $tmp1 = new stdClass();
    $tmp1->x = floor(($gain_min_value > $loss_min_value ? $gain_min_value : $loss_min_value) / 10) * 10;
    $tmp1->y = $tmp1->x;
    $data2[] = $tmp1;
    $tmp1 = new stdClass();
    $tmp1->x = ceil(($gain_max_value > $loss_max_value ? $loss_max_value : $gain_max_value) / 10) * 10;
    $tmp1->y = $tmp1->x;
    $data2[] = $tmp1;

    $datasets[] = ['type' => 'scatter',
                   'data' => $data2,
                   'showLine' => true,
                   'borderColor' => 'black',
                   'borderWidth' => 1,
                   'fill' => false,
                   'pointRadius' => 0,
                   'label' => '',
                  ];

    // error_log($log_prefix . print_r($datasets, true));

    $scales = new stdClass();
    $scales->xAxes[] = ['display' => true,
                        'scaleLabel' => ['display' => true,
                                         'labelString' => '得点',
                                         'fontColor' => 'black',
                                        ],
                       ];
    $scales->yAxes[] = ['display' => true,
                        'bottom' => $loss_min_value,
                        'scaleLabel' => ['display' => true,
                                         'labelString' => '失点',
                                         'fontColor' => 'black',
                                        ],
                       ];
    $data = ['type' => 'bubble',
             'data' => ['datasets' => $datasets],
             'options' => ['legend' => ['position' => 'bottom',
                                        'labels' => ['fontSize' => 10,
                                                     'fontColor' => 'black',
                                                    ],
                                       ],
                           'scales' => $scales,
                           'annotation' => ['annotations' => [['type' => 'line',
                                                               'mode' => 'vertical',
                                                               'scaleID' => 'x-axis-0',
                                                               'value' => $gain_avg,
                                                               'borderColor' => 'black',
                                                               'borderWidth' => 1,
                                                              ],
                                                              ['type' => 'line',
                                                               'mode' => 'horizontal',
                                                               'scaleID' => 'y-axis-0',
                                                               'value' => $loss_avg,
                                                               'borderColor' => 'black',
                                                               'borderWidth' => 1,
                                                              ],
                                                             ],
                                           ],
                           'animation' => ['duration' => 0,],
                           'hover' => ['animationDuration' => 0,],
                           'responsiveAnimationDuration' => 0,
                          ],
            ];
    $url = 'https://quickchart.io/chart?width=600&height=345&c=' . json_encode($data);
    $res = $mu_->get_contents($url);

    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    $im2 = imagecreatetruecolor(imagesx($im1) / 2, imagesy($im1) / 2 - 25);
    imagealphablending($im2, false);
    imagesavealpha($im2, true);
    imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 2, imagesy($im1) / 2 - 25, imagesx($im1), imagesy($im1) - 50);
    imagedestroy($im1);

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
    // $mu_->post_blog_wordpress('api.tinify.com', 'Compression count : ' . $match[1] . "\r\n" . 'Limits 500/month');
    $json = json_decode($tmp[1]);
    error_log($log_prefix . print_r($json, true));

    $url = $json->output->url;
    $options = [CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => 'api:' . getenv('TINYPNG_API_KEY'),
               ];
    $res = $mu_->get_contents($url, $options);
    $description = '<img src="data:image/png;base64,' . base64_encode($res) . '" />';

    $mu_->post_blog_hatena('Score Map', $description);
    $mu_->post_blog_fc2('Score Map', $description);

    $description = '<![CDATA[' . $description . ']]>';

    $rss_item_text = <<< __HEREDOC__
<item>
<guid isPermaLink="false">__HASH__</guid>
<pubDate />
<title>Score Map</title>
<link>http://dummy.local/</link>
<description>__DESCRIPTION__</description>
</item>
__HEREDOC__;

    $rss_item_text = str_replace('__DESCRIPTION__', $description, $rss_item_text);
    $rss_item_text = str_replace('__HASH__', hash('sha256', $description), $rss_item_text);
    file_put_contents($file_name_rss_items_, $rss_item_text, FILE_APPEND);
}

function make_loggly_usage($mu_, $file_name_rss_items_)
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

    unlink($cookie);

    foreach (json_decode($res)->total as $item) {
        error_log($log_prefix . date('m/d', $item[0] / 1000) . ' ' . round($item[1] / 1024 / 1024) . 'MB');
        // $labels[] = date('m/d', $item[0] / 1000);
        $labels[] = date('d', $item[0] / 1000);
        $data[] = round($item[1] / 1024 / 1024);
    }
    // array_pop($labels);
    // array_pop($data);

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
    imagedestroy($im1);
    $file = tempnam('/tmp', md5(microtime(true)));
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

    $mu_->post_blog_hatena('Loggly usage', $description);
    $mu_->post_blog_fc2('Loggly usage', $description);

    $description = '<![CDATA[' . $description . ']]>';

    $rss_item_text = <<< __HEREDOC__
<item>
<guid isPermaLink="false">__HASH__</guid>
<pubDate />
<title>Loggly usage</title>
<link>http://dummy.local/</link>
<description>__DESCRIPTION__</description>
</item>
__HEREDOC__;

    $rss_item_text = str_replace('__DESCRIPTION__', $description, $rss_item_text);
    $rss_item_text = str_replace('__HASH__', hash('sha256', $description), $rss_item_text);
    file_put_contents($file_name_rss_items_, $rss_item_text, FILE_APPEND);
}

function make_usage_graph($mu_, $file_name_rss_items_, $target_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

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
    // $mu_->post_blog_wordpress('api.tinify.com', 'Compression count : ' . $match[1] . "\r\n" . 'Limits 500/month');
    $json = json_decode($tmp[1]);
    error_log($log_prefix . print_r($json, true));

    $url = $json->output->url;
    $options = [CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => 'api:' . getenv('TINYPNG_API_KEY'),
               ];
    $res = $mu_->get_contents($url, $options);
    $description = '<img src="data:image/png;base64,' . base64_encode($res) . '" />';
    $mu_->post_blog_hatena(strtolower($target_) . ' usage', $description);
    $mu_->post_blog_fc2(strtolower($target_) . ' usage', $description);
    $description = '<![CDATA[' . $description . ']]>';

    $rss_item_text = <<< __HEREDOC__
<item>
<guid isPermaLink="false">__HASH__</guid>
<pubDate />
<title>__TITLE__</title>
<link>http://dummy.local/</link>
<description>__DESCRIPTION__</description>
</item>
__HEREDOC__;

    $rss_item_text = str_replace('__TITLE__', strtolower($target_) . ' quota', $rss_item_text);
    $rss_item_text = str_replace('__DESCRIPTION__', $description, $rss_item_text);
    $rss_item_text = str_replace('__HASH__', hash('sha256', $description), $rss_item_text);
    file_put_contents($file_name_rss_items_, $rss_item_text, FILE_APPEND);
}
