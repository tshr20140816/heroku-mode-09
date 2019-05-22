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
SELECT to_char(T1.check_time, 'YYYY/MM/DD') check_date
      ,MIN(T1.balance) balance
  FROM t_waon_history T1
 GROUP BY to_char(T1.check_time, 'YYYY/MM/DD')
 ORDER BY to_char(T1.check_time, 'YYYY/MM/DD') DESC
 LIMIT 40
;
__HEREDOC__;
    
    $pdo = $mu_->get_pdo();
    
    $labels = [];
    $data1 = [];
    foreach ($pdo->query($sql) as $row) {
        error_log(print_r($row, true));
        error_log(date('m/d', strtotime($row['check_date'])));
        $labels[$row['check_date']] = date('m/d', strtotime($row['check_date']));
        $tmp = new stdClass();
        $tmp->x = date('m/d', strtotime($row['check_date']));
        $tmp->y = $row['balance'];
        $data1[] = $tmp;
    }
    $pdo = null;
    
    ksort($labels);
    $labels = array_values($labels);
    
    error_log(print_r($labels, true));
}
