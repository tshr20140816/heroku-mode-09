<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));
$mu = new MyUtils();
$rc = check_library_rental_ok($mu, $requesturi);
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function check_library_rental_ok($mu_, $requesturi_)
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
    
    $tmp = explode(',', $list_lib_id[1]);
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
    $res = preg_replace('/\n+/s', "\n", $res);
    $res = str_replace('<li><span class="icon-available" style="word-wrap:break-word;">貸出可能</span></li>', '', $res);
    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $res, $matches);
    
    $list_ok = [];
    // error_log(print_r($matches[1], true));
    foreach ($matches[1] as $item) {
        // error_log($log_prefix . trim(strip_tags($item)));
        if (mb_strpos($item, '利用可能') === false) {
            continue;
        }
        $rc = preg_match('/(.+?)\n(.*?)\n/s', trim(strip_tags($item)), $match);
        $list_ok[] = mb_convert_encoding(mb_convert_kana($match[1] . $match[2], 'asKV'), 'utf-8');
    }
    $content = implode("\n", $list_ok);
    error_log($log_prefix . $content);
    error_log($log_prefix . hash('sha512', $content));
    
    $mu_->post_blog_wordpress('rental', hash('sha512', $content) . "\n" . $content);
}
