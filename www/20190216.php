<?php




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
    file_put_contents($file_name_blog_, "\nQuota : ${quota}\n\n", FILE_APPEND);
}
