<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
$rc = func_test2($mu, '/tmp/dummy');
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_test2($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
    
    $sql = <<< __HEREDOC__
SELECT M1.lib_id
      ,M1.lib_password
      ,M1.symbol
  FROM m_lib_account M1
 ORDER BY M1.symbol
;
__HEREDOC__;
    
    $pdo = $mu_->get_pdo();
    $list_lib_id = [];
    foreach ($pdo->query($sql) as $row) {
        $list_lib_id[] = base64_decode($row['lib_id'])
            . ',' . base64_decode($row['lib_password'])
            . ',' . $row['symbol'];
    }
    $pdo = null;
    
    $tmp = explode(',', $list_lib_id[0]);
    $lib_id = $tmp[0];
    $lib_password = $tmp[1];
    $symbol = $tmp[2];
    
    $cookie = tempnam("/tmp", md5(microtime(true)));
    $options1 = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
    ];
    
    $url = $mu_->get_env('URL_LIB');
    $res = $mu_->get_contents($url, $options1);
    $rc = preg_match('/<form name="LoginForm" method="post" action="(.+?)"/', $res, $match);
    
    $url = 'https://' . parse_url($url)['host'] . $match[1];
    
    $post_data = [
        'txt_usercd' => $lib_id,
        'txt_password' => $lib_password,
        'submit_btn_login' => 'ログイン',
        ];
    
    $options2 = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
    ];
    
    $res = $mu_->get_contents($url, $options2);
    
    $options3 = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_POST => true,
    ];
    $url = 'https://' . parse_url($url)['host'] . '/winj/opac/reserve-list.do';
    $res = $mu_->get_contents($url, $options3);
    
    // error_log($res);
    $res = preg_replace('/^\r\n$/s', '', $res);
    $res = preg_replace('/^\n$/s', '', $res);
    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $res, $matches);
    
    error_log(print_r($matches[1], true));
}
