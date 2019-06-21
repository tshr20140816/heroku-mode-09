<?php

class MyUtils
{
    private $_access_token = null;
    public $_count_web_access = 0;

    public function get_decrypt_string($encrypt_base64_string_)
    {
        $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

        $method = 'aes-256-cbc';
        $key = getenv('ENCRYPT_KEY');
        $iv = hex2bin(substr(hash('sha512', $key), 0, openssl_cipher_iv_length($method) * 2));
        return openssl_decrypt($encrypt_base64_string_, $method, $key, 0, $iv);
    }

    public function get_encrypt_string($original_string_)
    {
        $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

        $method = 'aes-256-cbc';
        $key = getenv('ENCRYPT_KEY');
        $iv = hex2bin(substr(hash('sha512', $key), 0, openssl_cipher_iv_length($method) * 2));
        return openssl_encrypt($original_string_, $method, $key, 0, $iv);
    }

    public function get_pdo()
    {
        $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

        $connection_info = parse_url(getenv('DATABASE_URL_TOODLEDO'));
        $pdo = new PDO(
            "pgsql:host=${connection_info['host']};dbname=" . substr($connection_info['path'], 1),
            $connection_info['user'],
            $connection_info['pass']
        );
        return $pdo;
    }

    public function get_env($key_name_, $is_decrypt_ = false)
    {
        $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

        if (apcu_exists(__METHOD__) === true) {
            $list_env = apcu_fetch(__METHOD__);
            error_log($log_prefix . '(CACHE HIT)$list_env');
        } else {
            $sql = <<< __HEREDOC__
SELECT T1.key
      ,T1.value
  FROM m_env T1
 ORDER BY T1.key
__HEREDOC__;

            $pdo = $this->get_pdo();

            $list_env = [];
            foreach ($pdo->query($sql) as $row) {
                $list_env[$row['key']] = $row['value'];
            }

            error_log($log_prefix . '$list_env : ' . print_r($list_env, true));
            $pdo = null;

            apcu_store(__METHOD__, $list_env);
        }
        $value = '';
        if (array_key_exists($key_name_, $list_env)) {
            $value = $list_env[$key_name_];
            if ($is_decrypt_ === true) {
                $value = $this->get_decrypt_string($value);
            }
        }
        return $value;
    }

    function post_blog_livedoor($title_, $description_ = null)
    {
        $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

        if (is_null($description_)) {
            $description_ = '.';
        }

        $livedoor_id = $this->get_env('LIVEDOOR_ID', true);
        $livedoor_atom_password = $this->get_env('LIVEDOOR_ATOM_PASSWORD', true);

        $xml = <<< __HEREDOC__
<?xml version="1.0" encoding="utf-8"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app">
  <title>__TITLE__</title>
  <content type="text/plain">__CONTENT__</content>
</entry>
__HEREDOC__;

        $xml = str_replace('__TITLE__', date('Y/m/d H:i:s', strtotime('+9 hours')) . " ${title_}", $xml);
        $xml = str_replace('__CONTENT__', htmlspecialchars(nl2br($description_)), $xml);

        $url = "https://livedoor.blogcms.jp/atompub/${livedoor_id}/article";

        $options = [
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => "${livedoor_id}:${livedoor_atom_password}",
            CURLOPT_HEADER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/atom+xml;type=entry', 'Expect:',],
        ];

        $res = $this->get_contents($url, $options);

        error_log($log_prefix . 'RESULT : ' . $res);

        // $this->update_ttrss();

        error_log($log_prefix . 'start exec');
        // exec('php -d apc.enable_cli=1 ../scripts/update_ttrss.php >/dev/null &');
        exec('php -d apc.enable_cli=1 -d include_path=.:/app/.heroku/php/lib/php:/app/lib ../scripts/update_ttrss.php >/dev/null &');
        error_log($log_prefix . 'finish exec');
    }

    public function get_contents($url_, $options_ = null, $is_cache_search = false)
    {
        $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';

        if ($is_cache_search !== true) {
            return $this->get_contents_nocache($url_, $options_);
        }

        if (is_null($options_) == false && array_key_exists(CURLOPT_POST, $options_) === true) {
            $url_base64 = base64_encode($url_ . '?' . $options_[CURLOPT_POSTFIELDS]);
        } else {
            $url_base64 = base64_encode($url_);
        }

        $sql = <<< __HEREDOC__
SELECT T1.url_base64
      ,T1.content_compress_base64
      ,T1.update_time
      ,CASE WHEN LOCALTIMESTAMP < T1.update_time + interval '22 hours' THEN 0 ELSE 1 END refresh_flag
  FROM t_webcache T1
 WHERE T1.url_base64 = :b_url_base64;
__HEREDOC__;

        $pdo = $this->get_pdo();

        $statement = $pdo->prepare($sql);

        $statement->execute([':b_url_base64' => $url_base64]);
        $result = $statement->fetchAll();

        if (count($result) === 0 || $result[0]['refresh_flag'] == '1') {
            $res = $this->get_contents_nocache($url_, $options_);
            $content_compress_base64 = base64_encode(gzencode($res, 9));

            $sql = <<< __HEREDOC__
DELETE
  FROM t_webcache
 WHERE url_base64 = :b_url_base64
    OR LOCALTIMESTAMP > update_time + interval '5 days';
__HEREDOC__;

            if (count($result) != 0) {
                $statement = $pdo->prepare($sql);
                $rc = $statement->execute([':b_url_base64' => $url_base64]);
                error_log($log_prefix . 'DELETE $rc : ' . $rc);
            }

            $sql = <<< __HEREDOC__
INSERT INTO t_webcache
( url_base64
 ,content_compress_base64
) VALUES (
  :b_url_base64
 ,:b_content_compress_base64
);
__HEREDOC__;
            if (strlen($res) > 0) {
                $statement = $pdo->prepare($sql);
                $rc = $statement->execute([':b_url_base64' => $url_base64,
                                           ':b_content_compress_base64' => $content_compress_base64]);
                error_log($log_prefix . 'INSERT $rc : ' . $rc);
            }
        } else {
            if (is_null($options_) == false && array_key_exists(CURLOPT_POST, $options_) === true) {
                error_log($log_prefix . '(CACHE HIT) url : ' . $url_ . '?' . $options_[CURLOPT_POSTFIELDS]);
            } else {
                error_log($log_prefix . '(CACHE HIT) url : ' . $url_);
            }
            $res = gzdecode(base64_decode($result[0]['content_compress_base64']));
        }
        $pdo = null;
        return $res;
    }

    public function get_contents_nocache($url_, $options_ = null)
    {
        $log_prefix = getmypid() . ' [' . __METHOD__ . '] ';
        error_log($log_prefix . 'URL : ' . $url_);
        error_log($log_prefix . 'options : ' . print_r($options_, true));

        $options = [
            CURLOPT_URL => $url_,
            CURLOPT_USERAGENT => getenv('USER_AGENT'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_PATH_AS_IS => true,
            CURLOPT_TCP_FASTOPEN => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS,
        ];

        if (is_null($options_) === false && array_key_exists(CURLOPT_USERAGENT, $options_)) {
            unset($options[CURLOPT_USERAGENT]);
        }

        $time_start = 0;
        $time_finish = 0;
        for ($i = 0; $i < 3; $i++) {
            $time_start = microtime(true);
            $ch = curl_init();
            $this->_count_web_access++;
            foreach ($options as $key => $value) {
                $rc = curl_setopt($ch, $key, $value);
                if ($rc == false) {
                    error_log($log_prefix . "curl_setopt : ${key} ${value}");
                }
            }
            if (is_null($options_) === false) {
                foreach ($options_ as $key => $value) {
                    $rc = curl_setopt($ch, $key, $value);
                    if ($rc == false) {
                        error_log($log_prefix . "curl_setopt : ${key} ${value}");
                    }
                }
            }
            $res = curl_exec($ch);
            $time_finish = microtime(true);
            $http_code = (string)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            error_log($log_prefix .
                      "HTTP STATUS CODE : ${http_code} [" .
                      substr(($time_finish - $time_start), 0, 5) . 'sec] ' .
                      parse_url($url_, PHP_URL_HOST));
            curl_close($ch);
            if (apcu_exists('HTTP_STATUS') === true) {
                $dic_http_status = apcu_fetch('HTTP_STATUS');
            } else {
                $dic_http_status = [];
            }
            if (array_key_exists($http_code, $dic_http_status) === true) {
                $dic_http_status[$http_code]++;
            } else {
                $dic_http_status[$http_code] = 1;
            }
            apcu_store('HTTP_STATUS', $dic_http_status);
            /*
            if ($http_code == '200' || $http_code == '201' || $http_code == '207' || $http_code == '303') {
                break;
            }
            */
            switch ($http_code) {
                case '200':
                case '201':
                case '207':
                case '302':
                case '303':
                    break 2;
            }

            error_log($log_prefix . '$res : ' . $res);
            $res = $http_code;

            if ($http_code != '503') {
                break;
            } else {
                sleep(3);
                error_log($log_prefix . 'RETRY URL : ' . $url_);
            }
        }

        error_log($log_prefix . 'LENGTH : ' . number_format(strlen($res)));
        return $res;
    }
}
