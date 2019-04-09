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

    $livedoor_id = $mu_->get_env('LIVEDOOR_ID', true);
    $url = "http://blog.livedoor.jp/${livedoor_id}/search?q=NOMA+Takayoshi";
    $res = $mu_->get_contents($url);
    
    $rc = preg_match('/<div class="article-body-inner">(.+?)<\/div>/s', $res, $match);
    $records = trim(strip_tags($match[1]));
    error_log($log_prefix . $records);
    
    $rc = preg_match_all('/(.+?) .+? (.+?).+/', $records, $matches);
    
    error_log(print_r($matches, true));
    
    /*
    $data = ['type' => 'line',
             'data' => ['labels' => ['03/29', '03/30', '03/31'],
                        'datasets' => [['label' => 'avg',
                                        'data' => [500, 500, 545],
                                        'fill' => false,
                                       ],
                                      ],
                       ],
            ];
    $url = 'https://quickchart.io/chart?width=300&height=100&c=' . json_encode($data);
    header('Content-Type: image/png');
    echo $mu_->get_contents($url);
    */
}
