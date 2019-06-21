#!/bin/bash

set -x

date

time pear channel-update pear.php.net
time pear install XML_RPC2

mkdir classes
chmod 755 ./start_web.sh

wait

date
