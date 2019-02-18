<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

backup_task($mu, '/tmp/dummy');

error_log(file_get_contents('/tmp/dummy'));

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function backup_task($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $cookie = tempnam("/tmp", time());

    $url = 'https://www.toodledo.com/signin.php?redirect=/tools/backup.php';

    $options = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1',
            ],
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_TIMEOUT => 20,
    ];

    $res = $mu_->get_contents($url, $options);

    $rc = preg_match('/<input .+? name="csrf1" value="(.*?)"/s', $res, $matches);
    $csrf1 = $matches[1];
    $rc = preg_match('/<input .+? name="csrf2" value="(.*?)"/s', $res, $matches);
    $csrf2 = $matches[1];

    $post_data = [
        'csrf1' => $csrf1,
        'csrf2' => $csrf2,
        'redirect' => '/tools/backup.php',
        'email' => base64_decode(getenv('TOODLEDO_EMAIL')),
        'pass' => base64_decode(getenv('TOODLEDO_PASSWORD')),
    ];

    $options = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1',
            ],
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
    ];

    $url = 'https://www.toodledo.com/signin.php';

    $res = $mu_->get_contents($url, $options);

    unlink($cookie);

    $task_count = preg_match_all('/<\/task>/', $res);

    $file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_tasks.txt';

    $file_size = $mu_->backup_data($res, $file_name);
    $file_size = number_format($file_size);
    
    file_put_contents($file_name_blog_, "Task backup size : ${file_size}Byte\nTask count : ${task_count}", FILE_APPEND);
}

function backup_opml($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $cookie = tempnam("/tmp", time());

    $url = 'https://www.inoreader.com/';

    $post_data = [
        'warp_action' => 'login',
        'hash_action' => '',
        'sendback' => '',
        'username' => base64_decode(getenv('INOREADER_USER')),
        'password' => base64_decode(getenv('INOREADER_PASSWORD')),
        'remember_me' => 'on',
    ];

    $options = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1',
            ],
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
    ];

    $res = $mu_->get_contents($url, $options);

    $url = 'https://www.inoreader.com/reader/subscriptions/export?download=1';

    $options = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1',
            ],
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_TIMEOUT => 20,
    ];

    $res = $mu_->get_contents($url, $options);
    
    unlink($cookie);

    $feed_count = preg_match_all('/ xmlUrl="/', $res);
    
    $file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_OPML.txt';

    $file_size = $mu_->backup_data($res, $file_name);
    $file_size = number_format($file_size);
    
    file_put_contents($file_name_blog_, "OPML backup size : ${file_size}Byte\nFeed count : ${feed_count}", FILE_APPEND);
}
