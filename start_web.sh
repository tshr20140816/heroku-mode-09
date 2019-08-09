#!/bin/bash

set -x

export TZ=JST-9
export WEB_CONCURRENCY=3
export USER_AGENT=$(curl https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/useragent.txt)
export DATABASE_URL=${DATABASE_URL_TOODLEDO}

if [ ! -v BASIC_USER ]; then
  echo "Error : BASIC_USER not defined."
  exit
fi

if [ ! -v BASIC_PASSWORD ]; then
  echo "Error : BASIC_PASSWORD not defined."
  exit
fi

if [ ! -v DATABASE_URL_TOODLEDO ]; then
  echo "Error : DATABASE_URL_TOODLEDO not defined."
  exit
fi

if [ ! -v ENCRYPT_KEY ]; then
  echo "Error : ENCRYPT_KEY not defined."
  exit
fi

if [ ! -v LOGGLY_TOKEN ]; then
  echo "Error : LOGGLY_TOKEN not defined."
  exit
fi

if [ ! -v TTRSS_USER ]; then
  echo "Error : TTRSS_USER not defined."
  exit
fi

if [ ! -v TTRSS_PASSWORD ]; then
  echo "Error : TTRSS_PASSWORD not defined."
  exit
fi

htpasswd -c -b .htpasswd ${BASIC_USER} ${BASIC_PASSWORD} &

fc-cache -fv &

pushd classes
# wget -q https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/classes/MyUtils.php &
curl -sS -O https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/classes/MyUtils.php &
popd

pushd scripts
# wget -q https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/scripts/update_ttrss.php &
# wget -q https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/scripts/chartjs_node.js &
curl -sS -O https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/scripts/update_ttrss.php \
         -O https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/scripts/chartjs_node.js &
popd

pushd www
# wget -q https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/check_train.php &
# wget -q https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/opcache_compile_file.php &
curl -sS -O https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/check_train.php \
         -O https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/opcache_compile_file.php &
popd

touch /tmp/php_error.txt

wait

printenv | wc -c

ls -lang classes
ls -lang scripts
ls -lang www

curl -s -m 1 --basic -u ${BASIC_USER}:${BASIC_PASSWORD} https://${HEROKU_APP_NAME}.herokuapp.com/opcache_compile_file.php

vendor/bin/heroku-php-apache2 -C apache.conf www
