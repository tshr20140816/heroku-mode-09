<?php
include(dirname(__FILE__) . '/../classes/MyUtils.php');
$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

func_20190422($mu, '/tmp/dummy.txt');
error_log("${pid} FINISH " . substr((microtime(true) - $time_start), 0, 6) . 's');

function func_20190422($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $cookie = tempnam("/tmp", 'cookie_' .  md5(microtime(true)));

    $url = 'https://www.waon.com/wmUseHistoryInq/mInit.do';

    $options1 = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1',
            ],
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
    ];

    $res = $mu_->get_contents($url, $options1);
    $res = mb_convert_encoding($res, 'UTF-8', 'SJIS');

    $rc = preg_match('/<input type="hidden" name="org.apache.struts.taglib.html.TOKEN" value="(.+?)"/s', $res, $match);
    $token = $match[1];

    $pdo = $mu_->get_pdo();

    $sql = <<< __HEREDOC__
SELECT T2.balance
      ,T2.last_use_date
  FROM t_waon_history T2
 WHERE T2.check_time = (SELECT MAX(T1.check_time) FROM t_waon_history T1)
__HEREDOC__;

    foreach ($pdo->query($sql) as $row) {
        $balance = (int)$row['balance'];
        $last_use_date = $row['last_use_date'];
    }

    $tmp = explode('-', $last_use_date);
    $last_use_date = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);
    $last_use_date_new = $last_use_date;

    $post_data = [
        'org.apache.struts.taglib.html.TOKEN' => $token,
        'cardNo' => $mu_->get_env('WAON_CARD_NO', true),
        'secNo' => $mu_->get_env('WAON_CODE', true),
        'magic' => '1',
    ];

    $url = 'https://www.waon.com/wmUseHistoryInq/mLogin.do';

    $options2 = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
            'Cache-Control: no-cache',
            'Connection: keep-alive',
            'DNT: 1',
            'Upgrade-Insecure-Requests: 1',
            ],
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
    ];

    $res = $mu_->get_contents($url, $options2);
    $res = mb_convert_encoding($res, 'UTF-8', 'SJIS');

    for ($i = 0; $i < 2; $i++) {
        $rc = preg_match('/<a href="\/wmUseHistoryInq\/mMoveMonth.do\?beforeMonth=0&amp;org.apache.struts.taglib.html.TOKEN=(.+?)"/s', $res, $match);
        $token = $match[1];

        $url = 'https://www.waon.com/wmUseHistoryInq/mMoveMonth.do?beforeMonth=' . $i . '&org.apache.struts.taglib.html.TOKEN=' . $token;

        $res = $mu_->get_contents($url, $options1);
        $res = mb_convert_encoding($res, 'UTF-8', 'SJIS');
        // error_log($res);

        $items = explode('<hr size="1">', $res);

        foreach ($items as $item) {
            if (strpos($item, '取引年月日') == false) {
                continue;
            }

            $rc = preg_match('/取引年月日<.+?><.+?>(.+?)</s', $item, $match);
            $tmp = trim($match[1]);
            $tmp = explode('/', $tmp);
            $use_date = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);

            $rc = preg_match('/利用金額<.+?><.+?>(.+?)円/s', $item, $match);
            $amount = (int)str_replace(',', '', trim($match[1]));

            if ($use_date > $last_use_date) {
                $balance -= $amount;
                if ($last_use_date_new < $use_date) {
                    $last_use_date_new = $use_date;
                }
            }

            error_log($log_prefix . date('Ymd', $use_date) . " ${amount} ${balance}");
        }
        if ((int)date('j', strtotime('+9 hours')) > 4) {
            break;
        }
    }

    $sql = <<< __HEREDOC__
INSERT INTO t_waon_history
( check_time
 ,balance
 ,last_use_date
) VALUES (
  TO_TIMESTAMP(:b_check_time, 'YYYY/MM/DD HH24:MI:SS')
 ,:b_balance
 ,TO_DATE(:b_last_use_date, 'YYYY/MM/DD')
)
__HEREDOC__;

    $statement = $pdo->prepare($sql);
    /*
    $rc = $statement->execute(
        [':b_check_time' => date('Y/m/d H:i:s', strtotime('+9 hours')),
         ':b_balance' => $balance,
         ':b_last_use_date' => date('Y/m/d', $last_use_date_new),
        ]);
    error_log($log_prefix . print_r($statement->errorInfo(), true));
    error_log($log_prefix . 'INSERT $rc : ' . $rc);
    */
    $pdo = null;

    unlink($cookie);

    $last_used = date('Y/m/d', $last_use_date_new);
    $balance = number_format($balance);
    file_put_contents($file_name_blog_, "\nWAON balance : ${balance}yen\nLast used : ${last_used}\n", FILE_APPEND);
}
