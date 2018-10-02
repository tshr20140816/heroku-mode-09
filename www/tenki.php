<?php

//$url = 'https://api.toodledo.com/3/account/authorize.php?response_type=code&client_id=' . getenv('TOODLEDO_CLIENTID') . '&state=' . getenv('TOODLEDO_SECRET') . '&scope=tasks';
//$res = file_get_contents($url);
//error_log($res);

$code = $_GET['code'];
$state = $_GET['state'];

error_log($code);
error_log($state);

$post_data = ['grant_type' => 'authorization_code', 'code' => $code];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://' . getenv('TOODLEDO_CLIENTID') . ':' . getenv('TOODLEDO_SECRET') . '@api.toodledo.com/3/account/token.php'); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
$res = curl_exec($ch);
curl_close($ch);

// error_log($res);

$params = json_decode($res, TRUE);
error_log($params['access_token']);

$res = file_get_contents('https://api.toodledo.com/3/tasks/get.php?access_token=' . $params['access_token'] . '&comp=0&folder=WEATHER');

error_log($res);

exit();

$res = file_get_contents('https://tenki.jp/week/' . getenv('LOCATION_NUMBER') . '/');

$rc = preg_match('/announce_datetime:(\d+-\d+-\d+)/', $res, $matches);

error_log($matches[0]);
error_log($matches[1]);

$dt = $matches[1];

$tmp = explode(getenv('POINT_NAME'), $res);
$tmp = explode('<td class="forecast-wrap">', $tmp[1]);

for ($i = 0; $i < 10; $i++) {
  // error_log(date('m/d', strtotime($dt . ' +' . $i . " day")));
  $list = explode("\n", str_replace(' ', '', trim(strip_tags($tmp[$i + 1]))));
  $tmp2 = $list[0];
  $tmp2 = str_replace('晴', '☼', $tmp2);
  $tmp2 = str_replace('曇', '☁', $tmp2);
  $tmp2 = str_replace('雨', '☂', $tmp2);
  $tmp2 = str_replace('のち', '/', $tmp2);
  $tmp2 = str_replace('時々', '|', $tmp2);
  $tmp2 = str_replace('一時', '|', $tmp2);
  error_log('+++++ ' . date('m/d', strtotime($dt . ' +' . $i . ' day')) . ' ' . $tmp2 . ' ' . $list[1] . ' ' . $list[2]. ' +++++');
}

?>
