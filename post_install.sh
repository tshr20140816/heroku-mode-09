#!/bin/bash

set -x

date

pear channel-update pear.php.net
pear install XML_RPC2 &

# ***** phppgadmin *****

pushd www
# git clone --depth 1 https://github.com/phppgadmin/phppgadmin.git phppgadmin
git clone --depth=1 -b REL_5-6-0  https://github.com/phppgadmin/phppgadmin.git phppgadmin
cp ../config.inc.php phppgadmin/conf/
# cp ../Connection.php phppgadmin/classes/database/
ls -lang phppgadmin
popd

# ***** XML_RPC2 *****

mkdir lib
pushd lib
git clone --depth=1 -b 1.1.4 https://github.com/pear/XML_RPC2.git .
pushd /tmp
mkdir pear_exception
pushd pear_exception
git clone --depth=1 https://github.com/pear/pear_exception.git .
popd
popd
cp -af /tmp/pear_exception/* ./
pushd /tmp
mkdir http_request2
pushd http_request2
git clone --depth=1 https://github.com/pear/http_request2.git .
popd
popd
cp -af /tmp/http_request2/* ./
pushd /tmp
mkdir net_url2
pushd net_url2
git clone --depth=1 https://github.com/pear/net_url2.git .
popd
popd
cp -af /tmp/net_url2/* ./

pushd /tmp
mkdir http_request
pushd http_request
git clone --depth=1 https://github.com/pear/http_request.git .
popd
popd
ls -lang
cp -af /tmp/http_request/* ./

rm -f *
ls -lang
popd

# https://stackoverflow.com/questions/3369675/php-idisk-webdav-client

wget -q https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.4.0/phpcs.phar
wget -q https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.4.0/phpcbf.phar

chmod 755 ./start_web.sh

wait

date
