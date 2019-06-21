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

export USER_AGENT=$(curl https://raw.githubusercontent.com/tshr20140816/heroku-mode-07/master/useragent.txt)

htpasswd -c -b .htpasswd ${BASIC_USER} ${BASIC_PASSWORD}

set +x
pushd classes
for file in $( ls . | grep .php$ ); do
  php -l ${file}
done
popd
pushd scripts
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

printenv > /tmp/printenv.txt
wc -c < /tmp/printenv.txt
rm /tmp/printenv.txt

ls -lang /tmp

wait

curl -s -m 1 --basic -u ${BASIC_USER}:${BASIC_PASSWORD} https://${HEROKU_APP_NAME}.herokuapp.com/opcache_compile_file.php

vendor/bin/heroku-php-apache2 -C apache.conf www
