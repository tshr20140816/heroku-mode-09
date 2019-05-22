<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190522($mu);

error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190522($mu_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $sql = <<< __HEREDOC__
SELECT T1.check_time
      ,T1.balance
  FROM t_waon_history T1
 ORDER BY T1.check_time DESC
 LIMIT 40
;
__HEREDOC__;
    
    $pdo = $mu_->get_pdo();
    
    foreach ($pdo->query($sql) as $row) {
        print_r($row, true);
    }
    $pdo = null;
}
