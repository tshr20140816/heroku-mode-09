#!/bin/bash

set -x

date

pear channel-update https://pear.php.net

pear install XML_RPC2 &

# ***** phppgadmin *****

pushd www
git clone --depth 1 https://github.com/phppgadmin/phppgadmin.git phppgadmin
cp ../config.inc.php phppgadmin/conf/
cp ../Connection.php phppgadmin/classes/database/
popd

wget -q https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.4.0/phpcs.phar
wget -q https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.4.0/phpcbf.phar

chmod 755 ./start_web.sh

wait

date
