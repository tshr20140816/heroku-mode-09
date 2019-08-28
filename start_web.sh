#!/bin/bash

set -x

export TZ=JST-9
export WEB_CONCURRENCY=4
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

curl -sS -o classes/MyUtils.php https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/classes/MyUtils.php \
         -o scripts/update_ttrss.php https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/scripts/update_ttrss.php \
         -o scripts/chartjs_node.js https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/scripts/chartjs_node.js \
         -o www/check_train.php https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/check_train.php \
         -o www/rainfall.php https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/rainfall.php \
         -o www/opcache_compile_file.php https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/opcache_compile_file.php &

touch /tmp/php_error.txt

wait

printenv | wc -c

ls -lang classes
ls -lang scripts
ls -lang www

curl -s -m 1 --basic -u ${BASIC_USER}:${BASIC_PASSWORD} https://${HEROKU_APP_NAME}.herokuapp.com/opcache_compile_file.php

vendor/bin/heroku-php-apache2 -C apache.conf www
