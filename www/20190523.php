<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190523b($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190523b($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $database_url = getenv('DATABASE_URL');
    
    $connection_info = parse_url($database_url);
    $database_name = substr($connection_info['path'], 1);
    $pdo = new PDO(
        "pgsql:host=${connection_info['host']};dbname=" . $database_name,
        $connection_info['user'],
        $connection_info['pass']
        );
    
    foreach ($pdo->query("SELECT pg_database_size('${database_name}') size") as $row) {
        error_log($log_prefix . print_r($row, true));
        error_log(round((int)$row['size'] / 1024 / 1024));
    }
    $pdo = null;
}

function func_20190523($mu_, $file_name_blog_, $target_ = 'TOODLEDO')
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    if ($target_ == 'TOODLEDO') {
        $heroku_app_name = getenv('HEROKU_APP_NAME');
        $database_url = getenv('DATABASE_URL');
    } else {
        $heroku_app_name = $mu_->get_env('HEROKU_APP_NAME_' . $target_);
        $database_url = getenv('DATABASE_URL_' . $target_);
    }
    $file_name = "/tmp/${heroku_app_name}_" .  date('d', strtotime('+9 hours')) . '_pg_dump.txt';
    error_log($log_prefix . $file_name);
    $cmd = "pg_dump --format=plain --dbname=${database_url} >${file_name}";
    exec($cmd);

    $file_size = $mu_->backup_data(file_get_contents($file_name), $file_name);
    $file_size = number_format($file_size);

    $sql = <<< __HEREDOC__
SELECT SUM(T1.reltuples) cnt
  FROM pg_class T1
 WHERE EXISTS ( SELECT 'X'
                  FROM pg_stat_user_tables T2
                 WHERE T2.relname = T1.relname
                   AND T2.schemaname='public'
              )
__HEREDOC__;

    // $pdo = $mu_->get_pdo();
    $connection_info = parse_url($database_url);
    $pdo = new PDO(
        "pgsql:host=${connection_info['host']};dbname=" . substr($connection_info['path'], 1),
        $connection_info['user'],
        $connection_info['pass']
        );

    $record_count = 0;
    foreach ($pdo->query($sql) as $row) {
        error_log($log_prefix . print_r($row, true));
        $record_count = $row['cnt'];
        // $record_count = number_format($record_count);
    }
    $pdo = null;

    $keyword = strtolower($target_);
    for ($i = 0; $i < strlen($keyword); $i++) {
        $keyword[$i] = chr(ord($keyword[$i]) + 1);
    }
    $keyword .= 'sfdpsedpvou';

    $description = '';
    $j = (int)date('j', strtotime('+9hours'));
    if ($j != 1) {
        $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
        $url = 'https://' . $hatena_blog_id . '/search?q=' . $keyword;
        $res = $mu_->get_contents($url);

        $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);
        $res = $mu_->get_contents($match[1]);

        $rc = preg_match('/<div class="' . $keyword . '">(.+?)</', $res, $match);
        $description = $match[1];
    }
    if (strpos($description, " ${j},") == false) {
        $description = '<div class="' . $keyword . '">' . trim("${description} ${j},${record_count}") . '</div>';
        $mu_->post_blog_wordpress($keyword, $description);
    }

    $record_count = number_format($record_count);
    file_put_contents($file_name_blog_,
                      "\nDatabase ${target_} backup size : ${file_size}Byte\nRecord count : ${record_count}\n", FILE_APPEND);
}

