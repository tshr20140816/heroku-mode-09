<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

get_record_count($mu, '/tmp/dummy');

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function get_record_count($mu_, $file_name_blog_)
{
    
    $pdo = $mu_->get_pdo();
    
    $sql = <<< __HEREDOC__
VACUUM t_webcache
__HEREDOC__;
    
    $pdo->exec($sql);
    
    $sql = <<< __HEREDOC__
SELECT SUM(T1.reltuples) cnt
  FROM pg_class T1
 WHERE EXISTS ( SELECT 'X'
                  FROM pg_stat_user_tables T2
                 WHERE T2.relname = T1.relname
                   AND T2.schemaname='public'
              )
__HEREDOC__;
    
    $count = 0;
    foreach ($pdo->query($sql) as $row) {
        error_log(print_r($row, true));
        $count = $row['cnt'];
    }
    
    error_log($count);
    
    $pdo = null;
}
