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

grep -c -e processor /proc/cpuinfo
cat /proc/cpuinfo | head -n 27

httpd -V
# httpd -M | sort
php --version
# whereis php
# php -m
cat /proc/version
cat /proc/cpuinfo | grep 'model name' | head -n 1
curl --version

echo "$(httpd -v)" > /tmp/apache_current_version
echo "$(php -v | head -n 1)" > /tmp/php_current_version
echo "$(curl -V | head -n 1)" > /tmp/curl_current_version

if [ $(date +%-M) -lt 10 ]; then
  # heroku-buildpack-php
  composer update > /dev/null 2>&1 &
fi

export USER_AGENT=$(curl https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/useragent.txt)

htpasswd -c -b .htpasswd ${BASIC_USER} ${BASIC_PASSWORD}

dig -t txt _netblocks.google.com

pushd www
mv ical.php ${ICS_ADDRESS}.php
popd

set +x
pushd classes
for file in $( ls . | grep .php$ ); do
  php -l ${file}
done
popd
pushd www
for file in $( ls . | grep .php$ ); do
  php -l ${file}
done
popd
set -x

wait

curl -s -m 1 --basic -u ${BASIC_USER}:${BASIC_PASSWORD} https://${HEROKU_APP_NAME}.herokuapp.com/opcache_compile_file.php

vendor/bin/heroku-php-apache2 -C apache.conf www
