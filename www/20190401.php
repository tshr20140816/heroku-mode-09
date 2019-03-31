<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$rc = apcu_clear_cache();
$mu = new MyUtils();

check_cloudme_usage($mu, '/tmp/dummy');

function check_cloudme_usage($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    $user_cloudme = $mu_->get_env('CLOUDME_USER', true);
    $password_cloudme = $mu_->get_env('CLOUDME_PASSWORD', true);
    $soap_text = <<< __HEREDOC__
<SOAP-ENV:Envelope
 xmlns:SOAPENV="http://schemas.xmlsoap.org/soap/envelope/"
 SOAP-ENV:encodingStyle=""
 xmlns:xsi="http://www.w3.org/1999/XMLSchema-instance"
 xmlns:xsd="http://www.w3.org/1999/XMLSchema">
<SOAP-ENV:Body>
<login></login>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
__HEREDOC__;
    $url = 'https://www.cloudme.com/v1/';
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
        CURLOPT_USERPWD => "${user_cloudme}:${password_cloudme}",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post_data,
        CURLOPT_HTTPHEADER => ['SOAPAction: login',
                               'Content-Type: text/xml; charset=utf-8',
                              ],
    ];
    $res = $mu_->get_contents($url, $options);
    error_log($res);
    $rc = preg_match('/<system>home<\/system><currentSize>(.+?)<\/currentSize><quotaLimit>(.+?)<\/quotaLimit>/s', $res, $match);
    error_log(print_r($match, true));
    $size = number_format($match[1]);
    $percentage = substr($match[1] / $match[2] * 100, 0, 5);
    error_log($log_prefix . "CloudMe usage : ${size}Byte ${percentage}%");
    file_put_contents($file_name_blog_, "\nCloudMe usage : ${size}Byte ${percentage}%\n\n", FILE_APPEND);
}
