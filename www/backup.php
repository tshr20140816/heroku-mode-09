<?php

include(dirname(__FILE__) . '/../classes/MyUtils.php');

$pid = getmypid();
$requesturi = $_SERVER['REQUEST_URI'];
$time_start = microtime(true);
error_log("${pid} START ${requesturi} " . date('Y/m/d H:i:s'));

$mu = new MyUtils();

$file_name_blog = '/tmp/blog.txt';
@unlink($file_name_blog);

// quota
get_quota($mu, $file_name_blog);

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

    $cookie = tempnam("/tmp", time());

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

    $cookie = tempnam("/tmp", time());

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

    $cookie = tempnam("/tmp", time());

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
                 'user[login]' => getenv('TEST3_USER'),
                 'user[password]' => getenv('TEST3_PASSWORD'),
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

function check_hidrive_usage($mu_, $file_name_blog_)
{
    $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

    $user = base64_decode(getenv('HIDRIVE_USER'));
    $password = base64_decode(getenv('HIDRIVE_PASSWORD'));

    $url = "https://webdav.hidrive.strato.com/users/${user}/";
    $options = [
        CURLOPT_ENCODING => 'gzip, deflate, br',
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "${user}:${password}",
        CURLOPT_HTTPHEADER => ['Connection: keep-alive',],
    ];
    $res = $mu_->get_contents($url, $options);

    $tmp = explode('<tbody>', $res)[1];
    $rc = preg_match_all('/<a href="(.+?)">/', $tmp, $matches);

    array_shift($matches[1]);

    $size = 0;
    $options = [
        CURLOPT_HTTPAUTH => CURLAUTH_ANY,
        CURLOPT_USERPWD => "${user}:${password}",
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_HTTPHEADER => ['Connection: keep-alive',],
    ];
    foreach ($matches[1] as $file_name) {
        $url = "https://webdav.hidrive.strato.com/users/${user}/" . $file_name;
        $urls[$url] = $options;
    }
    $res = $mu_->get_contents_multi($urls, null);

    foreach ($res as $result) {
        $rc = preg_match('/Content-Length: (\d+)/', $result, $match);
        $size += (int)$match[1];
    }
    $size = number_format($size);

    error_log($log_prefix . "Hidrive usage : ${size}Byte");
    file_put_contents($file_name_blog_, "\nHidrive usage : ${size}Byte\n\n", FILE_APPEND);
}
