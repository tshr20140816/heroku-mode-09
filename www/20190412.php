<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190412($mu, '/tmp/dummy');

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190412($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_pg_dump.txt';
    error_log($log_prefix . $file_name);
    $cmd = 'pg_dump --format=plain --dbname=' . getenv('DATABASE_URL') . ' >' . $file_name;
    exec($cmd);

    /*
    $file_size = $mu_->backup_data(file_get_contents($file_name), $file_name);
    $file_size = number_format($file_size);
    */
    $base_name = pathinfo($file_name)['basename'];
    $user_hidrive = $mu_->get_env('HIDRIVE_USER', true);
    $password_hidrive = $mu_->get_env('HIDRIVE_PASSWORD', true);
    
    exec("gpg --batch --passphrase-fd 0 --symmetric ${file_name} <<< testpasswordabc99");
    $res = file_get_contents($file_name . '.gpg');
    error_log($log_prefix . strlen(base64_encode($res)));
    
    return;
    
    // $res = bzcompress($data_, 9);
    exec("xz -9 ${file_name}");
    $res = file_get_contents($file_name . '.xz');
    error_log($log_prefix . strlen($res));
    
    $method = 'aes-256-cbc';
    $password = base64_encode($user_hidrive) . base64_encode($password_hidrive);
    $iv = substr(sha1($file_name_), 0, openssl_cipher_iv_length($method));
    $res = openssl_encrypt($res, $method, $password, OPENSSL_RAW_DATA, $iv);
    $res = base64_encode($res);
    error_log($log_prefix . $base_name . ' size : ' . number_format(strlen($res)));
    file_put_contents($file_name, $res);

    $sql = <<< __HEREDOC__
SELECT SUM(T1.reltuples) cnt
  FROM pg_class T1
 WHERE EXISTS ( SELECT 'X'
                  FROM pg_stat_user_tables T2
                 WHERE T2.relname = T1.relname
                   AND T2.schemaname='public'
              )
__HEREDOC__;

    $pdo = $mu_->get_pdo();
    $record_count = 0;
    foreach ($pdo->query($sql) as $row) {
        error_log($log_prefix . print_r($row, true));
        $record_count = $row['cnt'];
        $record_count = number_format($record_count);
    }
    $pdo = null;

    file_put_contents($file_name_blog_, "\nDatabase backup size : ${file_size}Byte\nRecord count : ${record_count}\n", FILE_APPEND);
}
