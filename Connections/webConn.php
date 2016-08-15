<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_webConn = "localhost";
$database_webConn = "rfp";
$username_webConn = "webuser";
$password_webConn = "webUser01";
$webConn = mysql_pconnect($hostname_webConn, $username_webConn, $password_webConn) or trigger_error(mysql_error(),E_USER_ERROR); 
?>