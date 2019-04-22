<?php

$ftp_link_id = ftp_connect(getenv('FC2_FTP_SERVER'));

$rc = ftp_login($ftp_link_id, getenv('FC2_FTP_ID'), getenv('FC2_FTP_PASSWORD'));
error_log('ftp_login : ' . $rc);

$rc = ftp_pasv($ftp_link_id, true);
error_log('ftp_pasv : ' . $rc);

$rc = ftp_nlist($ftp_link_id, '.');
error_log(print_r($rc, true));

$rc = ftp_close($ftp_link_id);
error_log('ftp_close : ' . $rc);
