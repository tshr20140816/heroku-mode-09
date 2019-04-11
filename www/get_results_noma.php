<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

get_results_noma($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function get_results_noma($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $livedoor_id = $mu_->get_env('LIVEDOOR_ID', true);
    $url = "http://blog.livedoor.jp/${livedoor_id}/search?q=NOMA+Takayoshi+" . date('Y');
    $res = $mu_->get_contents($url);

    $rc = preg_match('/<div class="article-body-inner">(.+?)<\/div>/s', $res, $match);
    $base_record = trim(strip_tags($match[1]));
    error_log($log_prefix . $base_record);

    $name = '野間 峻祥';
    $title = 'NOMA Takayoshi';
    $timestamp = strtotime('-1 day');
    $timestamp = strtotime('-2 day');

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
        if (strpos($match[0], '広島') >= 0) {
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
        if (strpos($data, $name) >= 0) {
            $rc = preg_match_all('/<tr.*?>(.+?)<\/tr>/s', $data, $matches);
            foreach ($matches[1] as $item) {
                if (strpos($item, $name) >= 0) {
                    $tmp = str_replace("\n", '', $item);
                    $tmp = preg_replace('/<.+?>/s', ' ', $tmp);
                    $tmp = str_replace($name, '', $tmp);
                    $tmp = date('Y/m/d', $timestamp) . ' ' . trim(preg_replace('/ +/', ' ', $tmp));
                    $description = $tmp . "\n" . $base_record;
                    error_log($log_prefix . $description);
                    $mu_->post_blog_wordpress($title, $description);
                    break 2;
                }
            }
        }
    }

    if ($description === '' ) {
        return;
    }

    $rc = preg_match_all('/(.+?) .+? (.+?) .+/', $description, $matches);
    $record_count = count($matches[0]);
    $labels = [];
    $data = [];
    for ($i = 0; $i < $record_count; $i++) {
        error_log($log_prefix . $matches[1][$record_count - $i - 1] . ' ' . $matches[2][$record_count - $i - 1]);
        $labels[] = substr($matches[1][$record_count - $i - 1], 5);
        $data[] = $matches[2][$record_count - $i - 1] * 1000;
    }

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
                          ],
            ];
    $url = 'https://quickchart.io/chart?c=' . json_encode($data);
    $res = $mu_->get_contents($url);

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
    error_log($log_prefix . 'Compression count :' . $match[1]);
    $json = json_decode($tmp[1]);
    error_log($log_prefix . print_r($json, true));

    $url = $json->output->url;
    $options = [CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => 'api:' . getenv('TINYPNG_API_KEY'),
               ];

    $res = $mu_->get_contents($url, $options);
    $description = '<img src="data:image/png;base64,' . base64_encode($res) . '" />';

    $mu_->post_blog_hatena('Batting Average', $description);
}
