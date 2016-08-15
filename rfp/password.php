<?php require_once('../Connections/webConn.php'); ?>
<?php require('../inc/functions.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

//initialize vars
$msg = '';

if ( isset($_POST['hid']) && $_POST['hid']=='pswdrecv' && $_POST['cEmail']!='' ) { // begin password recovery
	mysql_select_db($database_webConn, $webConn);
	$query_rsPassword = sprintf("SELECT demographics.username, demographics.passhint, demographics.`delete` FROM demographics WHERE demographics.Email=%s",
		GetSQLValueString($_POST['cEmail'], "text"));
	$rsPassword = mysql_query($query_rsPassword, $webConn) or die(mysql_error());
	$row_rsPassword = mysql_fetch_assoc($rsPassword);
	$totalRows_rsPassword = mysql_num_rows($rsPassword);	
	
	if ($totalRows_rsPassword > 0) { //show if recordset not empty
		if ($rsPassword['delete']==1) {
			$msp = '<p>The account associated with this eMail has been deactivated; please contact the Admin to re-activate.</p>';
		} else {
			//email credentials
			  //ini_set('SMTP', 'mail.domain.com');
			  //ini_set('sendmail_from', 'email@domain.com');
			  $to = $_POST['cEmail'];
			  $subject = 'RFP Contractor Access - Password Recovery';
			  $message = '<div style="font:12px Arial,Helvetica,san-serif">' . "\r\n";
			  $message .= '<p>You are registered on the Construction RFP web site with the following credentials:</p>' . "\r\n";
			  $message .= "\r\n";
			  $message .= '<p>Username: <strong>'. $row_rsPassword['username'] . '</strong><br />' ."\r\n";
			  $message .= 'Password:  &nbsp;<strong>'. $row_rsPassword['passhint'] . '</strong></p>' ."\r\n";
			  $message .= "\r\n";
			  $message .= '<p>Visit the Construction RFP website (http://localhost) and login to view construction projects and details.</p>';
			  $message .= '</div>';

			  // Always set content-type when sending HTML email
			  $headers = "MIME-Version: 1.0" . "\r\n";
			  $headers .= "Content-type:text/html; charset=iso-8859-1" . "\r\n";
			  // More headers
			  $headers .= "From: email@domain.com" . "\r\n";
			  // formating correction for Windows
			  $message = str_replace("\n.","\n..",$message); 
			  // use wordwrap for lines longer than 70 characters
			  $message = wordwrap($message,70); 

			  $mailcheck = checkmail($_POST['cEmail']);
			  if ( $mailcheck==TRUE ) { //send email
				  mail($to, $subject, $message, $headers);
				  $msg = '<p>Your account information has been forwarded. Please check your eMail for more information.</p><p><a href="login.php">Login</a></p>';
			  }
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RFP | Password Recovery</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="/inc/popup.js"></script>
<style type="text/css">
/*@import url("/inc/default.css");*/
@import url("/inc/child.css");
@import url("/rfp/rfp.css");
fieldset { border-color: transparent; }
</style>
</head>

<body>
<div id="layout">
<?php require('../inc/header.php'); ?>
<div id="content">
<div id="topslide" class="rfp">
<p><span></span></p>
</div>
<div id="content_layout" class="allspan">
<h2><span>Contractor Access</span></h2>
<div id="login">
<?php 
if ($totalRows_rsPassword > 0) { //show if recordset not empty 
	echo $msg;
} else { //show if recordset is empty or default page
	if (isset($_POST['hid']) && $_POST['cEmail']<>'') {
		$msg = 'No account was found registered with provided address';
	}
?>
<form action="password.php" method="POST" id="precovery">
<fieldset>
<legend>Password Recovery</legend>
<p>Forgotten your RFP username or password? Simply provide your email and we'll forward your login credentials to you.</p>
<table border="0" cellspacing="1" cellpadding="4" summary="password recovery form">
<tr>
<td class="lbl"><label for="cEmail">eMail Address</label></td>
<td><input name="cEmail" type="text" id="cEmail" size="35" maxlength="150" value="<?php if (isset($_POST['cEmail']) && $_POST['cEmail']!='') { echo($_POST['cEmail']); } ?>" /></td>
</tr>
<?php 
if ( $msg <> '' ) {
	echo('<tr><td></td><td><span style="font-size:11px;color:red">'.$msg.'</span></td></tr>');
}
?>
</table>
<input type="hidden" id="hid" name="hid" value="pswdrecv" />
<p style="margin:5px 0 0 285px;"><input name="submit" type="submit" class="btn" id="submit" value="Submit" /></p>
</fieldset>
</form>
<div class="warn">
<p>WARNING: This section of the Construction RFP web site is for authorized personnel only.<br />
All violators will be prosecuted to the full extent of the law.</p>
</div>
<?php } //end password recovery check ?>
</div>
</div>
</div>
<?php require('../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsPassword);
?>
