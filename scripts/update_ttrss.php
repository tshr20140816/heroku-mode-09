<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$time_start = microtime(true);
error_log("${pid} START scripts/update_ttrss.php " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

update_ttrss2($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function update_ttrss2($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => getenv('BASIC_USER') . ':' . getenv('BASIC_PASSWORD'),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json',],
        CURLOPT_POST => true,
    ];

    $url = $mu_->get_env('URL_TTRSS_1') . 'api/';
    $login_user = base64_decode(getenv('TTRSS_USER'));
    $login_password = base64_decode(getenv('TTRSS_PASSWORD'));
    $json = '{"op":"login","user":"' . $login_user .'","password":"' . $login_password . '"}';
    $res = $mu_->get_contents($url, $options + [CURLOPT_POSTFIELDS => $json,]);
    $data = json_decode($res);
    $session_id = $data->content->session_id;

    $urls = [];

    $livedoor_id = $mu_->get_env('LIVEDOOR_ID', true);
    $urls[] = "http://blog.livedoor.jp/${livedoor_id}/atom.xml";

    $fc2_fqdn = $this->get_env('FC2_FTP_SERVER', true);
    $urls[] = "https://${fc2_fqdn}/" . getenv('FC2_RSS_01') . '.xml';
    $urls[] = "https://${fc2_fqdn}/" . getenv('FC2_RSS_02') . '.xml';
    $urls[] = "https://${fc2_fqdn}/" . getenv('FC2_RSS_03') . '.xml';

    $json = '{"sid":"' . $session_id . '","op":"getFeeds","cat_id":-3}';
    $res = $mu_->get_contents($url, $options + [CURLOPT_POSTFIELDS => $json,]);
    $data = json_decode($res);
    foreach ($data->content as $feed) {
        error_log($log_prefix . 'feed_url : ' . $feed->feed_url);
        error_log($log_prefix . 'feed_id : ' . $feed->id);
        if (in_array($feed->feed_url, $urls)) {
            $json = '{"sid":"' . $session_id . '","op":"updateFeed","feed_id":' . $feed->id . '}';
            $res = $mu_->get_contents($url, $options + [CURLOPT_POSTFIELDS => $json,]);
            error_log($log_prefix . print_r(json_decode($res), true));
        }
    }
}