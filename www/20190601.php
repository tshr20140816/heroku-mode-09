<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_rss_items = tempnam('/tmp', 'rss_' . md5(microtime(true)));
@unlink($file_name_rss_items);

$rc = func_20190601($mu, $file_name_rss_items);

$time_finish = microtime(true);

error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');
exit();

function func_20190601($mu_, $file_name_rss_items_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $ftp_link_id = ftp_connect(getenv('TEST_FTP_SERVER'));
    
    $rc = ftp_login($ftp_link_id, getenv('TEST_FTP_ID'), getenv('TEST_FTP_PASSWORD'));
    error_log('ftp_login : ' . $rc);
    
    // $rc = ftp_pasv($ftp_link_id, true);
    // error_log('ftp_pasv : ' . $rc);
    
    // $rc = ftp_chdir($ftp_link_id, '/root');
    // error_log('ftp_chdir : ' . $rc);

    // $rc = ftp_nlist($ftp_link_id, '/root');
    // error_log(print_r($rc, true));
    
    $file_name = '/app/phpcs.phar';
    error_log('file size : ' . filesize($file_name));
    
    $rc = ftp_put($ftp_link_id, pathinfo($file_name)['basename'], $file_name, FTP_BINARY);
    error_log('ftp_put : ' . $rc);
    
    $rc = ftp_close($ftp_link_id);
    error_log('ftp_close : ' . $rc);
}
