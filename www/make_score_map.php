<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

make_score_map($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function make_score_map($mu_)
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

    $loss_sum = 0;
    $gain_sum = 0;
    $loss_min_value = 9999;
    for ($i = 0; $i < 12; $i++) {
        $gain_sum += (int)$matches[1][$i * 13 + 7];
        $loss_sum += (int)$matches[1][$i * 13 + 8];
        if ($loss_min_value > (int)$matches[1][$i * 13 + 8]) {
            $loss_min_value = (int)$matches[1][$i * 13 + 8];
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
    $url = 'https://quickchart.io/chart?width=600&height=320&c=' . json_encode($data);
    $res = $mu_->get_contents($url);

    $im1 = imagecreatefromstring($res);
    error_log($log_prefix . imagesx($im1) . ' ' . imagesy($im1));
    $im2 = imagecreatetruecolor(imagesx($im1) / 2, imagesy($im1) / 2);
    imagealphablending($im2, false);
    imagesavealpha($im2, true);
    imagecopyresampled($im2, $im1, 0, 0, 0, 0, imagesx($im1) / 2, imagesy($im1) / 2, imagesx($im1), imagesy($im1));

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

    $mu_->post_blog_hatena('Score Map', $description);
    $mu_->post_blog_fc2('Score Map', $description);

    $description = '<![CDATA[' . $description . ']]>';

    $xml_text = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
<title>Score Map</title>
<link>http://dummy.local/</link>
<description>Score Map</description>
<item>
<guid isPermaLink="false">__HASH__</guid>
<pubDate />
<title>Score Map</title>
<link>http://dummy.local/</link>
<description>__DESCRIPTION__</description>
</item>
</channel>
</rss>
__HEREDOC__;

    $xml_text = str_replace('__DESCRIPTION__', $description, $xml_text);
    $xml_text = str_replace('__HASH__', hash('sha256', $description), $xml_text);
    $file_name = '/tmp/' . getenv('FC2_RSS_03') . '.xml';
    file_put_contents($file_name, $xml_text);
    $mu_->upload_fc2($file_name);
    unlink($file_name);
}