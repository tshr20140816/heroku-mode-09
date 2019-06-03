#!/bin/bash

set -x

date

grep -c -e processor /proc/cpuinfo
cat /proc/cpuinfo | head -n $(($(cat /proc/cpuinfo | wc -l) / $(grep -c -e processor /proc/cpuinfo)))

pear channel-update pear.php.net
pear install XML_RPC2 &

# ***** phppgadmin *****

pushd www
# git clone --depth 1 https://github.com/phppgadmin/phppgadmin.git phppgadmin
git clone --depth=1 -b REL_5-6-0  https://github.com/phppgadmin/phppgadmin.git phppgadmin
cp ../config.inc.php phppgadmin/conf/
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

rm -f *
ls -lang
popd

wget -q https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.4.2/phpcs.phar
wget -q https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.4.2/phpcbf.phar

chmod 755 ./start_web.sh

wait

date
