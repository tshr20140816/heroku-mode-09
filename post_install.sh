#!/bin/bash

set -x

date

time pear channel-update pear.php.net
time pear install XML_RPC2

curl -sS -O https://oscdl.ipa.go.jp/IPAexfont/ipaexg00401.zip

mkdir .fonts
mv ipaexg00401.zip .fonts/
pushd .fonts
unzip ipaexg00401.zip
rm ipaexg00401.zip
popd
ls -lang .fonts/

mkdir classes
chmod 755 ./start_web.sh

date
