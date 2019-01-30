<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

if (!isset($_GET['n'])
    || $_GET['n'] === ''
    || is_array($_GET['n'])
    || !ctype_digit($_GET['n'])
   ) {
    error_log("${pid} FINISH Invalid Param");
    exit();
}

check_lib($mu, (int)$_GET['n']);

$time_finish = microtime(true);
$mu->post_blog_wordpress($requesturi . ' ' . substr(($time_finish - $time_start), 0, 6) . 's');
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

function check_lib($mu_, $order_) {
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

    if (count($list_lib_id) === 0 || count($list_lib_id) <= $order_) {
        error_go($log_prefix . 'DATA NOT FOUND');
        return;
    }
    
    $tmp = explode(',', $list_lib_id[$order_]);
    $lib_id = $tmp[0];
    $lib_password = $tmp[1];
    $symbol = $tmp[2];
    
    $cookie = $tmpfname = tempnam("/tmp", time());

    $options1 = [
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
    ];
    
    $url = getenv('LIB_URL');
    $res = $mu_->get_contents($url, $options1);

    $rc = preg_match('/<form name="LoginForm" method="post" action="(.+?)"/', $res, $match);
    
    $url = 'https://' . parse_url(getenv('LIB_URL'))['host'] . $match[1];
    
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
    
    $ok = 0;
    $rc = preg_match('/<LI style="float:none;">利用可能な資料があります。（(\d+)冊）<\/LI>/s', $res, $match);
    if ($rc === 1) {
        $ok = $match[1];
    }
    
    $rental = 0;
    $rc = preg_match('/<dd>現在、借受中の資料です。<.*?<p class="number"><span>(\d+?)</s', $res, $match);
    if ($rc === 1) {
        $rental = $match[1];
    }
    
    $reserve = 0;
    $rc = preg_match('/<dd>予約状況を確認できます。<.*?<p class="number"><span>(\d+?)</s', $res, $match);
    if ($rc === 1) {
        $reserve = $match[1];
    }
    
    $basket = 0;
    $rc = preg_match('/<dd>予約かごに入れた資料を確認できます。<.*?<p class="number"><span>(\d+?)</s', $res, $match);
    if ($rc === 1) {
        $basket = $match[1];
    }
    
    unlink($cookie);
    
    // add task
    
    $access_token = $mu_->get_access_token();
    $folder_id_label = $mu_->get_folder_id('LABEL');
    $list_context_id = $mu_->get_contexts();
    
    $list_add_task[] = '{"title":"' . "${symbol} / ${ok} / ${rental} / ${reserve} / ${basket}"
      . '","duedate":"' . mktime(0, 0, 0, 1, 6, 2018)
      . '","context":"' . $list_context_id[date('w', mktime(0, 0, 0, 1, 6, 2018))]
      . '","tag":"HOURLY","folder":"' . $folder_id_label . '"}';
    
    error_log($log_prefix . 'LIB : ' . print_r($list_add_task, true));
    
    // $rc = $mu_->add_tasks($list_add_task);
    
    // next
    
    if (count($list_lib_id) <= $order_ + 1) {
        return;
    }
    
    $url = 'https://' . getenv('HEROKU_APP_NAME') . '.herokuapp.com/lib_info.php?n=' . ($order_ + 1);
    $options3 = [
        CURLOPT_TIMEOUT => 3,
        CURLOPT_USERPWD => getenv('BASIC_USER') . ':' . getenv('BASIC_PASSWORD'),
    ];
    
    $res = $mu_->get_contents($url, $options3);
}
