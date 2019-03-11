<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$rc = apcu_clear_cache();

$mu = new MyUtils();

$file_name_blog = '/tmp/blog.txt';
@unlink($file_name_blog);

// quota
get_quota($mu, $file_name_blog);

// WAON balance check
check_waon_balance($mu, $file_name_blog);

// Database Backup
backup_db($mu, $file_name_blog);

// Task Backup
backup_task($mu, $file_name_blog);

// OPML Backup
backup_opml($mu, $file_name_blog);

// OPML2 Backup
backup_opml2($mu, $file_name_blog);

// HiDrive usage
check_hidrive_usage($mu, $file_name_blog);

// pCloud usage
check_pcloud_usage($mu, $file_name_blog);

// TeraCLOUD usage
check_teracloud_usage($mu, $file_name_blog);

// OpenDrive usage
check_opendrive_usage($mu, $file_name_blog);

// apache version check
check_version_apache($mu, $file_name_blog);

// php version check
check_version_php($mu, $file_name_blog);

// curl version check
check_version_curl($mu, $file_name_blog);

//

$pdo = $mu->get_pdo();
$rc = $pdo->exec('TRUNCATE t_webcache');
error_log($pid . ' TRUNCATE t_webcache $rc : ' . $rc);
$pdo = null;

//

$url = 'http://otn.fujitv.co.jp/b_hp/918200222.html';
$urls_is_cache[$url] = null;

//

$url = $mu->get_env('URL_SOCCER_TEAM_CSV_FILE');
$urls_is_cache[$url] = null;

//

$url = 'http://www.carp.co.jp/_calendar/list.html';
$urls_is_cache[$url] = null;

//

$yyyy = date('Y');
$url = "https://e-moon.net/calendar_list/calendar_moon_${yyyy}/";
$urls_is_cache[$url] = null;

//

$url = 'https://www.w-nexco.co.jp/traffic_info/construction/traffic.php?fdate='
    . date('Ymd', strtotime('+1 day'))
    . '&tdate='
    . date('Ymd', strtotime('+14 day'))
    . '&ak=1&ac=1&kisei%5B%5D=901&dirc%5B%5D=1&dirc%5B%5D=2&order=2&ronarrow=0'
    . '&road%5B%5D=1011&road%5B%5D=1912&road%5B%5D=1020&road%5B%5D=225A&road%5B%5D=1201'
    . '&road%5B%5D=1222&road%5B%5D=1231&road%5B%5D=234D&road%5B%5D=1232&road%5B%5D=1260';
$urls_is_cache[$url] = null;

//

$url = 'https://github.com/apache/httpd/releases.atom?4nocache' . date('Ymd', strtotime('+9 hours'));
$urls_is_cache[$url] = null;

//

$url = 'https://github.com/php/php-src/releases.atom?4nocache' . date('Ymd', strtotime('+9 hours'));
$urls_is_cache[$url] = null;

//

$url = 'https://github.com/curl/curl/releases.atom?4nocache' . date('Ymd', strtotime('+9 hours'));
$urls_is_cache[$url] = null;

//

$url = 'https://devcenter.heroku.com/articles/php-support?4nocache' . date('Ymd', strtotime('+9 hours'));
$urls_is_cache[$url] = null;

//

$options = [CURLOPT_HTTPHEADER => ['Accept: application/vnd.heroku+json; version=3',
                                   'Authorization: Bearer ' . base64_decode(getenv('HEROKU_API_KEY')),
                                   ]];
$url = 'https://api.heroku.com/account';
$urls_is_cache[$url] = $options;

//

$url = 'https://map.yahooapis.jp/geoapi/V1/reverseGeoCoder?output=json&appid='
    . $mu->get_env('YAHOO_API_KEY', true)
    . '&lon=' . $mu->get_env('LONGITUDE') . '&lat=' . $mu->get_env('LATITUDE');
$urls_is_cache[$url] = null;

//

$start_yyyy = date('Y');
$start_m = date('n');
$finish_yyyy = date('Y', strtotime('+3 month'));
$finish_m = date('n', strtotime('+3 month'));

$url = 'http://calendar-service.net/cal?start_year=' . $start_yyyy
    . '&start_mon=' . $start_m . '&end_year=' . $finish_yyyy . '&end_mon=' . $finish_m
    . '&year_style=normal&month_style=numeric&wday_style=ja_full&format=csv&holiday_only=1&zero_padding=1';
$urls_is_cache[$url] = null;

//

$timestamp = strtotime('+1 day');
$yyyy = date('Y', $timestamp);
$mm = date('m', $timestamp);
$url = "https://eco.mtk.nao.ac.jp/koyomi/dni/${yyyy}/m" . $mu->get_env('AREA_ID') . "${mm}.html";
$urls_is_cache[$url] = null;

// multi
$list_contents = $mu->get_contents_multi(null, $urls_is_cache);

//

$yyyy = date('Y');
$yyyy++;
$url = "https://e-moon.net/calendar_list/calendar_moon_${yyyy}/";
$res = $mu->get_contents($url, null, true);

//

$options = [
    CURLOPT_ENCODING => 'gzip, deflate, br',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: ja,en-US;q=0.7,en;q=0.3',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'DNT: 1',
        'Upgrade-Insecure-Requests: 1',
        ],
];

for ($i = 0; $i < 8; $i++) {
    $url = $mu->get_env('URL_BUS_0' . ($i + 1)) . '&4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu->get_contents($url, $options, true);
}

//

for ($j = 0; $j < 4; $j++) {
    $yyyy = date('Y', strtotime('+' . $j . ' years'));

    $url = 'http://calendar-service.net/cal?start_year=' . $yyyy
        . '&start_mon=1&end_year=' . $yyyy . '&end_mon=12'
        . '&year_style=normal&month_style=numeric&wday_style=ja_full&format=csv&holiday_only=1&zero_padding=1';

    $res = $mu->get_contents($url, null, true);
}

//

$yyyy = (int)date('Y');
for ($j = 0; $j < 2; $j++) {
    $post_data = ['from_year' => $yyyy];

    $res = $mu->get_contents(
        'http://www.calc-site.com/calendars/solar_year',
        [CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
        ],
        true
    );

    $yyyy++;
}

//

$area_id = $mu->get_env('AREA_ID');
for ($j = 0; $j < 4; $j++) {
    $timestamp = strtotime(date('Y-m-01') . " +${j} month");
    $yyyy = date('Y', $timestamp);
    $mm = date('m', $timestamp);
    $res = $mu->get_contents("https://eco.mtk.nao.ac.jp/koyomi/dni/${yyyy}/s${area_id}${mm}.html", null, true);
}

//

$sub_address = $mu->get_env('SUB_ADDRESS');
for ($i = 11; $i > -1; $i--) {
    $url = 'https://feed43.com/' . $sub_address . ($i * 5 + 11) . '-' . ($i * 5 + 15) . '.xml';
    $res = $mu->get_contents($url, null, true);
}

$time_finish = microtime(true);
$mu->post_blog_wordpress("${requesturi} [" . substr(($time_finish - $time_start), 0, 6) . 's]',
                        file_get_contents($file_name_blog));
@unlink($file_name_blog);
error_log("${pid} FINISH " . substr(($time_finish - $time_start), 0, 6) . 's ' . substr((microtime(true) - $time_start), 0, 6) . 's');

exit();

function get_quota($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $api_key = base64_decode(getenv('HEROKU_API_KEY'));
    $url = 'https://api.heroku.com/account';

    $res = $mu_->get_contents(
        $url,
        [CURLOPT_HTTPHEADER => ['Accept: application/vnd.heroku+json; version=3',
                                "Authorization: Bearer ${api_key}",
                               ]],
        true
    );

    $data = json_decode($res, true);
    error_log($log_prefix . '$data : ' . print_r($data, true));
    $account = explode('@', $data['email'])[0];
    $url = "https://api.heroku.com/accounts/${data['id']}/actions/get-quota";

    $res = $mu_->get_contents(
        $url,
        [CURLOPT_HTTPHEADER => ['Accept: application/vnd.heroku+json; version=3.account-quotas',
                                "Authorization: Bearer ${api_key}",
        ]]
    );

    $data = json_decode($res, true);
    error_log($log_prefix . '$data : ' . print_r($data, true));

    $dyno_used = (int)$data['quota_used'];
    $dyno_quota = (int)$data['account_quota'];

    error_log($log_prefix . '$dyno_used : ' . $dyno_used);
    error_log($log_prefix . '$dyno_quota : ' . $dyno_quota);

    $quota = $dyno_quota - $dyno_used;
    $quota = floor($quota / 86400) . 'd ' . ($quota / 3600 % 24) . 'h ' . ($quota / 60 % 60) . 'm';

    file_put_contents($file_name_blog_, "\nQuota : ${quota}\n", FILE_APPEND);
}

function check_waon_balance($mu_, $file_name_blog_)
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
            $amount = (int)trim($match[1]);

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
    $rc = $statement->execute(
        [':b_check_time' => date('Y/m/d H:i:s', strtotime('+9 hours')),
         ':b_balance' => $balance,
         ':b_last_use_date' => date('Y/m/d', $last_use_date_new),
        ]);
    error_log($log_prefix . print_r($statement->errorInfo(), true));
    error_log($log_prefix . 'INSERT $rc : ' . $rc);
    $pdo = null;

    unlink($cookie);

    $last_used = date('Y/m/d', $last_use_date_new);
    $balance = number_format($balance);
    file_put_contents($file_name_blog_, "\nWAON balance : ${balance}yen\nLast used : ${last_used}\n", FILE_APPEND);
}

function backup_db($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_pg_dump.txt';
    error_log($log_prefix . $file_name);
    $cmd = 'pg_dump --format=plain --dbname=' . getenv('DATABASE_URL') . ' >' . $file_name;
    exec($cmd);

    $file_size = $mu_->backup_data(file_get_contents($file_name), $file_name);
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

    $pdo = $mu_->get_pdo();
    $record_count = 0;
    foreach ($pdo->query($sql) as $row) {
        error_log($log_prefix . print_r($row, true));
        $record_count = $row['cnt'];
        $record_count = number_format($record_count);
    }
    $pdo = null;

    file_put_contents($file_name_blog_, "\nDatabase backup size : ${file_size}Byte\nRecord count : ${record_count}\n", FILE_APPEND);
}

function backup_task($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $cookie = tempnam("/tmp", 'cookie_' .  md5(microtime(true)));

    $url = 'https://www.toodledo.com/signin.php?redirect=/tools/backup.php';

    $options = [
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
        CURLOPT_TIMEOUT => 20,
    ];

    $res = $mu_->get_contents($url, $options);

    $rc = preg_match('/<input .+? name="csrf1" value="(.*?)"/s', $res, $matches);
    $csrf1 = $matches[1];
    $rc = preg_match('/<input .+? name="csrf2" value="(.*?)"/s', $res, $matches);
    $csrf2 = $matches[1];

    $post_data = [
        'csrf1' => $csrf1,
        'csrf2' => $csrf2,
        'redirect' => '/tools/backup.php',
        'email' => base64_decode(getenv('TOODLEDO_EMAIL')),
        'pass' => base64_decode(getenv('TOODLEDO_PASSWORD')),
    ];

    $options = [
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
        CURLOPT_TIMEOUT => 20,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
    ];

    $url = 'https://www.toodledo.com/signin.php';

    $res = $mu_->get_contents($url, $options);

    unlink($cookie);

    $task_count = preg_match_all('/<\/task>/', $res);
    $task_count = number_format($task_count);

    $file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_tasks.txt';

    $file_size = $mu_->backup_data($res, $file_name);
    $file_size = number_format($file_size);

    file_put_contents($file_name_blog_, "\nTask backup size : ${file_size}Byte\nTask count : ${task_count}\n", FILE_APPEND);
}

function backup_opml($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $cookie = tempnam("/tmp", 'cookie_' .  md5(microtime(true)));

    $url = 'https://www.inoreader.com/';

    $post_data = [
        'warp_action' => 'login',
        'hash_action' => '',
        'sendback' => '',
        'username' => base64_decode(getenv('INOREADER_USER')),
        'password' => base64_decode(getenv('INOREADER_PASSWORD')),
        'remember_me' => 'on',
    ];

    $options = [
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
        CURLOPT_TIMEOUT => 20,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
    ];

    $res = $mu_->get_contents($url, $options);

    $url = 'https://www.inoreader.com/reader/subscriptions/export?download=1';

    $options = [
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
        CURLOPT_TIMEOUT => 20,
    ];

    $res = $mu_->get_contents($url, $options);

    unlink($cookie);

    $feed_count = preg_match_all('/ xmlUrl="/', $res);

    $file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_OPML.txt';

    $file_size = $mu_->backup_data($res, $file_name);
    $file_size = number_format($file_size);

    file_put_contents($file_name_blog_, "\nOPML backup size : ${file_size}Byte\nFeed count : ${feed_count}\n", FILE_APPEND);
}

function backup_opml2($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $cookie = tempnam("/tmp", 'cookie_' .  md5(microtime(true)));

    $url = 'https://theoldreader.com/users/sign_in';

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

    $rc = preg_match('/"authenticity_token".+?value="(.+?)"/', $res, $match);

    error_log($log_prefix . print_r($match, true));

    $post_data = ['authenticity_token' => $match[1],
                 'utf8' => '&#x2713;',
                 'user[login]' => base64_decode(getenv('THEOLDREADER_USER')),
                 'user[password]' => base64_decode(getenv('THEOLDREADER_PASSWORD')),
                 ];

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

    $url = 'https://theoldreader.com/feeds.opml';

    $res = $mu_->get_contents($url, $options1);

    // error_log($log_prefix . $res);

    unlink($cookie);

    $feed_count = preg_match_all('/ xmlUrl="/', $res);

    $file_name = '/tmp/' . getenv('HEROKU_APP_NAME')  . '_' .  date('d', strtotime('+9 hours')) . '_OPML2.txt';

    $file_size = $mu_->backup_data($res, $file_name);
    $file_size = number_format($file_size);

    file_put_contents($file_name_blog_, "\nOPML2 backup size : ${file_size}Byte\nFeed count : ${feed_count}\n", FILE_APPEND);
}

function check_opendrive_usage($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $user_opendrive = base64_decode(getenv('OPENDRIVE_USER'));
    $password_opendrive = base64_decode(getenv('OPENDRIVE_PASSWORD'));

    $url = 'https://dev.opendrive.com/api/v1/session/login.json';
    $post_data = [
        'username' => $user_opendrive,
        'passwd' => $password_opendrive,
        'version' => '1',
        'partner_id' => '',
    ];
    $options = [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post_data),
    ];
    $res = $mu_->get_contents($url, $options);
    $data = json_decode($res);
    $session_id = $data->SessionID;

    $url = "https://dev.opendrive.com/api/v1/users/info.json/${session_id}";
    $res = $mu_->get_contents($url);
    $data = json_decode($res);
    $size = number_format($data->StorageUsed);

    error_log($log_prefix . "OpenDrive usage : ${size}Byte");
    file_put_contents($file_name_blog_, "\nOpenDrive usage : ${size}Byte\n\n", FILE_APPEND);
}

function check_teracloud_usage($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $user_teracloud = base64_decode(getenv('TERACLOUD_USER'));
    $password_teracloud = base64_decode(getenv('TERACLOUD_PASSWORD'));
    $api_key_teracloud = base64_decode(getenv('TERACLOUD_API_KEY'));
    $node_teracloud = base64_decode(getenv('TERACLOUD_NODE'));

    $url = "https://${node_teracloud}.teracloud.jp/v2/api/dataset/(property)";
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${user_teracloud}:${password_teracloud}",
        CURLOPT_HTTPHEADER => ["X-TeraCLOUD-API-KEY: ${api_key_teracloud}",],
    ];
    $res = $mu_->get_contents($url, $options);

    $data = json_decode($res);
    $size = number_format($data->dataset->__ROOT__->used);

    error_log($log_prefix . "TeraCLOUD usage : ${size}Byte");
    file_put_contents($file_name_blog_, "\nTeraCLOUD usage : ${size}Byte\n\n", FILE_APPEND);
}

function check_pcloud_usage($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $user_pcloud = $this->get_env('PCLOUD_USER', true);
    $password_pcloud = $this->get_env('PCLOUD_PASSWORD', true);

    $url = "https://api.pcloud.com/userinfo?getauth=1&logout=1&username=${user_pcloud}&password=${password_pcloud}";
    $res = $mu_->get_contents($url);

    $data = json_decode($res);
    $size = number_format($data->usedquota);

    error_log($log_prefix . "pCloud usage : ${size}Byte");
    file_put_contents($file_name_blog_, "\npCloud usage : ${size}Byte\n\n", FILE_APPEND);
}

function check_hidrive_usage($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $user_hidrive = base64_decode(getenv('HIDRIVE_USER'));
    $password_hidrive = base64_decode(getenv('HIDRIVE_PASSWORD'));

    $url = "https://webdav.hidrive.strato.com/users/${user_hidrive}/";
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${user_hidrive}:${password_hidrive}",
        CURLOPT_HTTPHEADER => ['Connection: keep-alive',],
    ];
    $res = $mu_->get_contents($url, $options);

    $tmp = explode('<tbody>', $res)[1];
    $rc = preg_match_all('/<a href="(.+?)">/', $tmp, $matches);

    array_shift($matches[1]);

    $size = 0;
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "${user_hidrive}:${password_hidrive}",
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_HTTPHEADER => ['Connection: keep-alive',],
    ];
    foreach ($matches[1] as $file_name) {
        $url = "https://webdav.hidrive.strato.com/users/${user_hidrive}/" . $file_name;
        $urls[$url] = $options;
    }
    $res = $mu_->get_contents_multi($urls, null);

    foreach ($res as $result) {
        $rc = preg_match('/Content-Length: (\d+)/', $result, $match);
        $size += (int)$match[1];
    }
    $size = number_format($size);

    error_log($log_prefix . "HiDrive usage : ${size}Byte");
    file_put_contents($file_name_blog_, "\nHiDrive usage : ${size}Byte\n\n", FILE_APPEND);
}

function check_version_apache($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'https://github.com/apache/httpd/releases.atom?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);

    $doc = new DOMDocument();
    $doc->loadXML($res);

    $xpath = new DOMXpath($doc);
    $xpath->registerNamespace('ns', 'http://www.w3.org/2005/Atom');

    $elements = $xpath->query("//ns:entry/ns:title");

    $list_version = [];
    foreach ($elements as $element) {
        $tmp = $element->nodeValue;
        $tmp = explode('.', $tmp);
        $list_version[(int)$tmp[0] * 1000000 + (int)$tmp[1] * 1000 + (int)$tmp[2]] = $element->nodeValue;
    }
    krsort($list_version);
    $version_latest = array_shift($list_version);

    $res = file_get_contents('/tmp/apache_current_version');
    $version_current = trim(str_replace(["\r\n", "\r", "\n", '   ', '  '], ' ', $res));

    $url = 'https://devcenter.heroku.com/articles/php-support?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);

    $rc = preg_match('/<strong><a href="http:\/\/httpd.apache.org">Apache<\/a>(.+?)<\/strong> \((.+?)\) and <strong>/s', $res, $match);
    $version_support = $match[2];

    error_log($log_prefix . '$version_latest : ' . $version_latest);
    error_log($log_prefix . '$version_support : ' . $version_support);
    error_log($log_prefix . '$version_current : ' . $version_current);

    // $mu_->post_blog_wordpress('Apache Version', "latest : ${version_latest}\nsupport : ${version_support}\ncurrent : ${version_current}");
    $content = "\nApache Version\nlatest : ${version_latest}\nsupport : ${version_support}\ncurrent : ${version_current}\n";
    file_put_contents($file_name_blog_, $content, FILE_APPEND);
}

function check_version_php($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'https://github.com/php/php-src/releases.atom?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);

    $doc = new DOMDocument();
    $doc->loadXML($res);

    $xpath = new DOMXpath($doc);
    $xpath->registerNamespace('ns', 'http://www.w3.org/2005/Atom');

    $elements = $xpath->query("//ns:entry/ns:title");

    $list_version = [];
    foreach ($elements as $element) {
        $tmp = $element->nodeValue;
        if (strpos($tmp, 'RC') > 0) {
            continue;
        }
        $tmp = str_replace('php-', '', $tmp);
        $tmp = explode('.', $tmp);
        $list_version[(int)$tmp[0] * 10000 + (int)$tmp[1] * 100 + (int)$tmp[2]] = $element->nodeValue;
    }
    krsort($list_version);
    error_log(print_r($list_version, true));
    $version_latest = array_shift($list_version);

    $res = file_get_contents('/tmp/php_current_version');
    $version_current = trim(str_replace(["\r\n", "\r", "\n", '   ', '  '], ' ', $res));

    $url = 'https://devcenter.heroku.com/articles/php-support?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);

    $rc = preg_match('/<h4 id="supported-versions-php">PHP<\/h4>.*?<ul>(.+?)<\/ul>/s', $res, $match);

    $rc = preg_match_all('/<li>(.+?)<\/li>/s', $match[1], $matches);

    $list_version = [];
    foreach ($matches[1] as $item) {
        $tmp = explode('.', $item);
        $list_version[$tmp[0] * 10000 + $tmp[1] * 100 + $tmp[2]] = $item;
    }
    krsort($list_version);

    $version_support = array_shift($list_version);

    error_log($log_prefix . '$version_latest : ' . $version_latest);
    error_log($log_prefix . '$version_support : ' . $version_support);
    error_log($log_prefix . '$version_current : ' . $version_current);

    $content = "\nPHP Version\nlatest : ${version_latest}\nsupport : ${version_support}\ncurrent : ${version_current}\n";
    file_put_contents($file_name_blog_, $content, FILE_APPEND);
}

function check_version_curl($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $url = 'https://github.com/curl/curl/releases.atom?4nocache' . date('Ymd', strtotime('+9 hours'));
    $res = $mu_->get_contents($url, null, true);

    $doc = new DOMDocument();
    $doc->loadXML($res);

    $xpath = new DOMXpath($doc);
    $xpath->registerNamespace('ns', 'http://www.w3.org/2005/Atom');

    $elements = $xpath->query("//ns:entry/ns:title");

    $version_latest = $elements[0]->nodeValue;

    $res = file_get_contents('/tmp/curl_current_version');
    $version_current = trim(str_replace(["\r\n", "\r", "\n", '   ', '  '], ' ', $res));

    error_log($log_prefix . '$version_latest : ' . $version_latest);
    error_log($log_prefix . '$version_current : ' . $version_current);

    $content = "\ncurl Version\nlatest : ${version_latest}\ncurrent : ${version_current}\n";
    file_put_contents($file_name_blog_, $content, FILE_APPEND);
}
