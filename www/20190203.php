<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

// imap.mail.yahoo.co.jp 993

backup_task($mu);

function backup_task($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $cookie = $tmpfname = tempnam("/tmp", time());

    $url = 'https://www.toodledo.com/signin.php?redirect=/tools/backup.php';

    $options1 = [
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

    $res = $mu_->get_contents($url, $options1);

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

    $options2 = [
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

    $res = $mu_->get_contents($url, $options2);

    error_log(strlen($res));

    unlink($cookie);
    
    $file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_tasks.txt';
    
    $res = bzcompress($res, 9);
    
    $method = 'AES-256-CBC';
    $password = base64_encode(getenv('HIDRIVE_USER')) . base64_encode(getenv('HIDRIVE_PASSWORD'));
    $IV = substr(sha1($file_name), 0, openssl_cipher_iv_length($method));
    $res = openssl_encrypt($res, $method, $password, OPENSSL_RAW_DATA, $IV);
    
    $res = base64_encode($res);
    error_log($log_prefix . 'file size : ' . strlen($res));
    file_put_contents($file_name, $res);
    
    $user = base64_decode(getenv('HIDRIVE_USER'));
    $password = base64_decode(getenv('HIDRIVE_PASSWORD'));
    
    $url = "https://webdav.hidrive.strato.com/users/${user}/" . pathinfo($file_name)['basename'];
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "${user}:${password}",
        CURLOPT_CUSTOMREQUEST => 'DELETE',
    ];
    $res = $mu_->get_contents($url, $options);
    
    $file_size = filesize($file_name);
    $fh = fopen($file_name, 'r');
    
    // $url = "https://webdav.hidrive.strato.com/users/${user}/" . pathinfo($file_name)['basename'];
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "${user}:${password}",
        CURLOPT_PUT => true,
        CURLOPT_INFILE => $fh,
        CURLOPT_INFILESIZE => $file_size,
    ];
    
    $res = $mu_->get_contents($url, $options);
    
    fclose($fh);
    
    unlink($file_name);
    
    $mu_->post_blog_wordpress('Task backup : ' . $file_size);
}
