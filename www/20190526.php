<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_rss_items = tempnam('/tmp', 'rss_' . md5(microtime(true)));
@unlink($file_name_rss_items);

func_20190526($mu, $file_name_rss_items);

$time_finish = microtime(true);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190526($mu_, $file_name_blog_)
{
    // backup_db_x($mu_, $file_name_blog_);
    backup_db_x($mu_, $file_name_blog_, 'TTRSS');
    // backup_db_x($mu_, $file_name_blog_, 'REDMINE');
}

function backup_db_x($mu_, $file_name_blog_, $target_ = 'TOODLEDO')
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    if ($target_ == 'TOODLEDO') {
        $heroku_app_name = getenv('HEROKU_APP_NAME');
        $database_url = getenv('DATABASE_URL');
    } else {
        $heroku_app_name = $mu_->get_env('HEROKU_APP_NAME_' . $target_);
        $database_url = $mu_->get_env('DATABASE_URL_' . $target_, true);
    }
    $file_name = "/tmp/${heroku_app_name}_" .  date('d', strtotime('+9 hours')) . '_pg_dump.txt';
    error_log($log_prefix . $file_name);
    $cmd = "pg_dump --format=plain --dbname=${database_url} >${file_name}";
    exec($cmd);
    $file_size = $mu_->backup_data(file_get_contents($file_name), $file_name);
    
    return;
    
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
    $database_name = substr($connection_info['path'], 1);
    $pdo = new PDO(
        "pgsql:host=${connection_info['host']};dbname=" . $database_name,
        $connection_info['user'],
        $connection_info['pass']
        );
    $record_count = 0;
    foreach ($pdo->query($sql) as $row) {
        error_log($log_prefix . print_r($row, true));
        $record_count = $row['cnt'];
    }
    
    $database_size = 0;
    foreach ($pdo->query("SELECT pg_database_size('${database_name}') size") as $row) {
        error_log($log_prefix . print_r($row, true));
        $database_size = $row['size'];
    }
    $pdo = null;
    $hatena_blog_id = $mu_->get_env('HATENA_BLOG_ID', true);
    $keyword = strtolower($target_);
    for ($i = 0; $i < strlen($keyword); $i++) {
        $keyword[$i] = chr(ord($keyword[$i]) + 1);
    }
    $keyword .= 'sfdpsedpvou';
    $description = '';
    $j = (int)date('j', strtotime('+9hours'));
    if ($j != 1) {
        $url = 'https://' . $hatena_blog_id . '/search?q=' . $keyword;
        $res = $mu_->get_contents($url);
        $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);
        $res = $mu_->get_contents($match[1]);
        $rc = preg_match('/<div class="' . $keyword . '">(.+?)</', $res, $match);
        $description = $match[1];
    }
    if (strpos($description, " ${j},") == false) {
        $description = '<div class="' . $keyword . '">' . trim("${description} ${j},${record_count}") . '</div>';
        // $mu_->post_blog_wordpress($keyword, $description);
        // $mu_->post_blog_wordpress_async($keyword, $description);
    }
    $keyword = strtolower($target_) . 'databasesize';
    for ($i = 0; $i < strlen($keyword); $i++) {
        $keyword[$i] = chr(ord($keyword[$i]) + 1);
        if ($keyword[$i] == '{') {
            $keyword[$i] = 'a';
        }
    }
    $description = '';
    $j = (int)date('j', strtotime('+9hours'));
    if ($j != 1) {
        $url = 'https://' . $hatena_blog_id . '/search?q=' . $keyword;
        $res = $mu_->get_contents($url);
        $rc = preg_match('/<a class="entry-title-link" href="(.+?)"/', $res, $match);
        $res = $mu_->get_contents($match[1]);
        $rc = preg_match('/<div class="' . $keyword . '">(.+?)</', $res, $match);
        $description = $match[1];
    }
    if (strpos($description, " ${j},") == false) {
        $description = '<div class="' . $keyword . '">' . trim("${description} ${j},${database_size}") . '</div>';
        // $mu_->post_blog_wordpress($keyword, $description);
        // $mu_->post_blog_wordpress_async($keyword, $description);
    }
    $record_count = number_format($record_count);
    $database_size = number_format($database_size);
    file_put_contents($file_name_blog_,
                      "\nDatabase ${target_} backup size : ${file_size}Byte\n" .
                      "Record count : ${record_count}\nDatabase size : ${database_size}Byte\n",
                      FILE_APPEND);
}
