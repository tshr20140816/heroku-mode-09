<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190409($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190409($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    // $url = 'https://tinypng.com/web/api';
    
    $livedoor_id = $mu_->get_env('LIVEDOOR_ID', true);
    $url = "http://blog.livedoor.jp/${livedoor_id}/search?q=NOMA+Takayoshi";
    $res = $mu_->get_contents($url);
    
    $rc = preg_match('/<div class="article-body-inner">(.+?)<\/div>/s', $res, $match);
    $records = trim(strip_tags($match[1]));
    error_log($log_prefix . $records);
    
    $rc = preg_match_all('/(.+?) .+? (.+?) .+/', $records, $matches);
    
    error_log(print_r($matches, true));
    
    $record_count = count($matches[0]);
    $labels = [];
    $data = [];
    for ($i = 0; $i < $record_count; $i++) {
        error_log($matches[1][$record_count - $i - 1] . ' ' . $matches[2][$record_count - $i - 1]);
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
    
    $url = 'https://tinypng.com/web/api';
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
    
    error_log($tmp[0]);
    $rc = preg_match('/compression-count: (.+)/i', $tmp[0], $match);
    error_log(print_r($match, true));
    $json = json_decode($tmp[1]);
    error_log(print_r($json, true));
    $url = $json->output->url;
    
    $options = [CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => 'api:' . getenv('TINYPNG_API_KEY'),
               ];    
    
    $res = $mu_->get_contents($url, $options);
    
    // header('Content-Type: image/png');
    // echo $res;

    // error_log(base64_encode($res));
    $description = '<img src="data:image/png;base64,' . base64_encode($res) . '" />';
    // $mu_->post_blog_hatena('TEST', $description);
    $mu_->post_blog_wordpress('Batting Average', $description, true);
}
