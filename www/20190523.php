<?php

$connection_info = parse_url(getenv('DATABASE_URL_REDMINE'));

$sql = <<< __HEREDOC__
SELECT SUM(T1.reltuples) cnt
  FROM pg_class T1
 WHERE EXISTS ( SELECT 'X'
                  FROM pg_stat_user_tables T2
                 WHERE T2.relname = T1.relname
                   AND T2.schemaname='public'
              )
__HEREDOC__;

$pdo = new PDO(
    "pgsql:host=${connection_info['host']};dbname=" . substr($connection_info['path'], 1),
    $connection_info['user'],
    $connection_info['pass']
);
$count_record = 0;
foreach ($pdo->query($sql) as $row) {
    error_log(print_r($row, true));
    $count_record = $row['cnt'];
}

$pdo = null;

header('Content-Type: text/plain');

echo $count_record;
