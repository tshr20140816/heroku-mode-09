#!/bin/bash

set -x

export TZ=JST-9

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

if [ ! -v TTRSS_USER ]; then
  echo "Error : TTRSS_PASSWORD not defined."
  exit
fi

export USER_AGENT=$(curl https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/useragent.txt)
export DATABASE_URL=${DATABASE_URL_TOODLEDO}
htpasswd -c -b .htpasswd ${BASIC_USER} ${BASIC_PASSWORD}

pushd classes
wget https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/classes/MyUtils.php
popd

pushd scripts
wget https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/scripts/update_ttrss.php
popd

pushd www
wget https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/check_train.php
wget https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/www/opcache_compile_file.php
popd

printenv > /tmp/printenv.txt
wc -c < /tmp/printenv.txt
rm /tmp/printenv.txt

ls -lang /tmp

curl -s -m 1 --basic -u ${BASIC_USER}:${BASIC_PASSWORD} https://${HEROKU_APP_NAME}.herokuapp.com/opcache_compile_file.php

vendor/bin/heroku-php-apache2 -C apache.conf www
