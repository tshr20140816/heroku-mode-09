<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_rss_items = '/tmp/rss_items.txt';
@unlink($file_name_rss_items);

make_usage_graph($mu, $file_name_rss_items, 'TOODLEDO');
// make_usage_graph($mu, $file_name_rss_items, 'TTRSS');

$xml_text = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
<title>heroku usage</title>
<link>http://dummy.local/</link>
<description>toodledo usage</description>
__ITEMS__
</channel>
</rss>
__HEREDOC__;

$file = '/tmp/' . getenv('FC2_RSS_02') . '.xml';
file_put_contents($file, str_replace('__ITEMS__', file_get_contents($file_name_rss_items), $xml_text));
$mu->upload_fc2($file);
unlink($file);
unlink($file_name_rss_items);

$time_finish = microtime(true);
$mu->post_blog_wordpress("${requesturi} [" . substr(($time_finish - $time_start), 0, 6) . 's]');

$url = 'https://' . getenv('HEROKU_APP_NAME') . '.herokuapp.com/get_results_batting.php';
$options = [
    CURLOPT_TIMEOUT => 3,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => getenv('BASIC_USER') . ':' . getenv('BASIC_PASSWORD'),
];
$mu->get_contents($url, $options);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

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
