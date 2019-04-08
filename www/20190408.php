<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();

check_zoho_usage20190408($mu, '/tmp/dummy');

function check_zoho_usage20190408($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $authtoken_zoho = $mu_->get_env('ZOHO_AUTHTOKEN', true);

    $url = "https://apidocs.zoho.com/files/v1/files?authtoken=${authtoken_zoho}&scope=docsapi";
    $res = $mu_->get_contents($url);

    $urls = [];
    $options = [CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
               ];
    foreach (json_decode($res)->FILES as $item) {
        $docid = $item->DOCID;
        $url = "https://apidocs.zoho.com/files/v1/content/${docid}?authtoken=${authtoken_zoho}&scope=docsapi";
        $urls[$url] = $options;
    }

    $multi_options = [
        CURLMOPT_PIPELINING => 3,
        CURLMOPT_MAX_HOST_CONNECTIONS => 10,
    ];
    $list_contents = $mu_->get_contents_multi($urls, null, $multi_options);
    error_log($log_prefix . 'memory_get_usage : ' . number_format(memory_get_usage()) . 'byte');

    $size = 0;
    foreach ($list_contents as $res) {
        $rc = preg_match('/Content-Length: (\d+)/', $res, $match);
        $size += (int)$match[1];
    }

    $percentage = substr($size / (5 * 1024 * 1024 * 1024) * 100, 0, 5);
    $size = number_format($size);

    error_log($log_prefix . "Zoho usage : ${size}Byte ${percentage}%");
    file_put_contents($file_name_blog_, "\nZoho usage : ${size}Byte ${percentage}%\n\n", FILE_APPEND);
}
