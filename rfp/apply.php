<?php require_once('../Connections/adminConn.php'); ?>
<?php require('../inc/functions.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

if ( isset($_REQUEST['pid']) ) {
	$pid = $_REQUEST['pid'];
} else {
	$pid = '';
}

if ( isset($_REQUEST['id']) ) {
	$id = $_REQUEST['id'];
} else {
	$id = '';
}
?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "demo")) {
	//validate input
	$err = "";
	if ( empty($_POST['fname']) ) $err .= "<li><strong>First Name</strong> requires a value</li>";
	if ( empty($_POST['lname']) ) $err .= "<li><strong>Last Name</strong> requires a value</li>";
	if ( empty($_POST['maddress']) ) $err .= "<li><strong>Address</strong> requires a value</li>";
	if ( empty($_POST['mcity']) ) $err .= "<li><strong>City</strong> requires a value</li>";
	if ( empty($_POST['mstate']) ) $err .= "<li><strong>State</strong> requires a value</li>";
	if ( empty($_POST['mzip']) ) $err .= "<li><strong>Zip Code</strong> requires a value</li>";
	if ( empty($_POST['cphone']) ) $err .= "<li><strong>Phone</strong> requires a value</li>";
	if ( empty($_POST['cemail']) ) {
		$err .= "<li><strong>Email</strong> requires a value</li>";
	} else if ( !filter_input(INPUT_POST,"cemail",FILTER_VALIDATE_EMAIL) ) {
		$err .= "<li><strong>Email</strong> has an invalid format</li>";
	}
	if (!empty($_POST['cemail'])) {
		//check if username is unique
		mysql_select_db($database_adminConn, $adminConn);
		$query_rsDupe = "SELECT Email FROM demographics ORDER BY Email ASC";
		$rsDupe = mysql_query($query_rsDupe, $adminConn) or die(mysql_error());
		$row_rsDupe = mysql_fetch_assoc($rsDupe);
		$totalRows_rsDupe = mysql_num_rows($rsDupe);mysql_select_db($database_adminConn, $adminConn);
		do {
			if ( strtolower($row_rsDupe['Email']) == strtolower($_POST['cemail']) ) $err .= "<strong>Email (".$_POST['cemail'].")</strong> already exists<br />";
		} while ($row_rsDupe = mysql_fetch_assoc($rsDupe));
		mysql_free_result($rsDupe);
	}
	if ( empty($_POST['uname']) ) $err .= "<li><strong>Username</strong> requires a value</li>";
	if (!empty($_POST['uname'])) {
		//check if username is unique
		mysql_select_db($database_adminConn, $adminConn);
		$query_rsDupe = "SELECT username FROM demographics ORDER BY username ASC";
		$rsDupe = mysql_query($query_rsDupe, $adminConn) or die(mysql_error());
		$row_rsDupe = mysql_fetch_assoc($rsDupe);
		$totalRows_rsDupe = mysql_num_rows($rsDupe);mysql_select_db($database_adminConn, $adminConn);
		do {
			if ( strtolower($row_rsDupe['username']) == strtolower($_POST['uname']) ) $err .= "<strong>Username (".$_POST['uname'].")</strong> already exists<br />";
		} while ($row_rsDupe = mysql_fetch_assoc($rsDupe));
		mysql_free_result($rsDupe);
	}

	$pswd = generatePassword();
	
	if ( $err == "" ) {
	  // save info to database
	  $submit_date = date('Y-m-d H:i:s');
	  $insertSQL = sprintf("INSERT INTO demographics (FirstName, LastName, Title, Email, Address, City, `State`, Zip, Company, Trade, Phone, Mobile, Fax, sect3, mbe, wbe, other, username, password, passhint) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
						   GetSQLValueString($_POST['fname'], "text"),
						   GetSQLValueString($_POST['lname'], "text"),
						   GetSQLValueString($_POST['ctitle'], "text"),
						   GetSQLValueString($_POST['cemail'], "text"),
						   GetSQLValueString($_POST['maddress'], "text"),
						   GetSQLValueString($_POST['mcity'], "text"),
						   GetSQLValueString($_POST['mstate'], "text"),
						   GetSQLValueString($_POST['mzip'], "text"),
						   GetSQLValueString($_POST['cname'], "text"),
						   GetSQLValueString($_POST['trade'], "text"),
						   GetSQLValueString($_POST['cphone'], "text"),
						   GetSQLValueString($_POST['cmobile'], "text"),
						   GetSQLValueString($_POST['cfax'], "text"),
						   GetSQLValueString(isset($_POST['s3']) ? "true" : "", "defined","'1'","'0'"),
						   GetSQLValueString(isset($_POST['mbe']) ? "true" : "", "defined","'1'","'0'"),
						   GetSQLValueString(isset($_POST['wbe']) ? "true" : "", "defined","'1'","'0'"),
						   GetSQLValueString($_POST['ocomment'], "text"),
						   GetSQLValueString($_POST['uname'], "text"), 
						   GetSQLValueString(better_crypt($pswd), "text"),
						   GetSQLValueString($pswd, "text"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($insertSQL, $adminConn) or die(mysql_error());
	  
	  //ini_set('SMTP', 'mail.domain.com');
	  //ini_set('sendmail_from', 'email@domain.com');
	  $to = $_POST['cemail'];
	  $subject = 'RFP Contractor Access';
	  $message = '<div style="font:12px Arial,Helvetica,san-serif">' . "\r\n";
	  $message .= '<p>You have been registered on the Construction RFP web site with the following credentials:</p>' . "\r\n";
	  $message .= "\r\n";
	  $message .= '<p>Username: &nbsp;<strong>'. $_POST['uname'] . '</strong><br />' . "\r\n";
	  $message .= 'Password:  &nbsp; <strong>'. $pswd . '</strong></p>' . "\r\n";
	  $message .= "\r\n";
	  $message .= '<p>Visit the Construction RFP website (http://localhost/) and login via the <em>Subcontractor Login</em> link to view construction projects and details.</p>' . "\r\n";
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
	
	  $mailcheck = checkmail($_POST['cemail']);
	  if ( $mailcheck==TRUE ) { //send email
		  mail($to,$subject,$message,$headers);
	  }
	
	  if (isset($_SESSION['func']) && $_SESSION['func']=='admin') {
		  $insertGoTo = "/rfp/admin/subusers.php";
		  /*if (isset($_SERVER['QUERY_STRING'])) {
			$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		  }*/
	  } else {
		  $insertGoTo = "apply.php";
		  if (isset($_SERVER['QUERY_STRING'])) {
			$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
			$insertGoTo .= $_SERVER['QUERY_STRING'];
			$insertGoTo .= "&pid=submitted";
		  }
	  }
	  header(sprintf("Location: %s", $insertGoTo));
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RFP | Request for Proposal: Apply</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/inc/popup.js"></script>
<style type="text/css">
/*@import url("../inc/default.css");*/
@import url("/inc/child.css");
@import url("rfp.css");
#rfp { width:85%; margin:1em auto; }
#rfp #msg { margin:50px auto 75px auto; }
#signup { padding-bottom:25px; }
</style>
</head>

<body>
<div id="layout">
<?php require('../inc/header.php'); ?>
<div id="content">
<div id="topslide" class="rfp">
<p><span>Request for Proposal (RFP)</span></p>
</div>
<h2><span>Contractor  Registration</span></h2>
<div id="rfp">
<?php if ( $pid == "submitted" ) { ?>
<div id="msg">
<h3><span>Registration complete.</span></h3>
<p>Thank you for your registration. Please check your email for username and password information.</p>
</div>
<?php } else { //display sign-up form ?>
<div id="signup">
<form name="demo" action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" id="demo">
<fieldset>
<p><span class="req">*</span> <span class="note">Denotes a required field</span></p>
<?php
if ( $err != "" ) {
	echo '<div class="err"><p>Application not submitted due to the following:</p><ul>';
	echo $err;
	echo '</ul></div>';
}
?>
<table border="0" cellspacing="1" cellpadding="4" summary="request for proposal form">
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="fname">First Name</label></td>
    <td><input name="fname" type="text" id="fname" size="30" maxlength="45" value="<?php if (isset($_POST['fname'])) echo $_REQUEST['fname']; ?>" /></td>
    <td class="lbl"><span class="req">*</span> <label for="lname">Last Name</label></td>
    <td><input name="lname" type="text" id="lname" size="35" maxlength="45" value="<?php if (isset($_POST['lname'])) echo $_REQUEST['lname']; ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><label for="ctitle">Title</label></td>
    <td><input name="ctitle" type="text" id="ctitle" size="30" maxlength="100" value="<?php if (isset($_POST['ctitle'])) echo $_REQUEST['ctitle']; ?>" /></td>
    <td class="lbl"><label for="cname">Company</label></td>
    <td><input name="cname" type="text" id="cname" size="35" maxlength="100" value="<?php if (isset($_POST['cname'])) echo $_REQUEST['cname']; ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="maddress">Address</label></td>
    <td colspan="3"><input name="maddress" type="text" id="maddress" size="88" maxlength="100" value="<?php if (isset($_POST['maddress'])) echo $_REQUEST['maddress']; ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="mcity">City</label></td>
    <td><input name="mcity" type="text" id="mcity" size="30" maxlength="75" value="<?php if (isset($_POST['mcity'])) echo $_REQUEST['mcity']; ?>" /></td>
    <td class="lbl"><span class="req">*</span> <label for="mstate">State</label></td>
<td><input name="mstate" type="text" id="mstate" size="8" maxlength="45" value="<?php if (isset($_POST['mstate'])) echo $_REQUEST['mstate']; ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      
<span class="req">*</span> <label for="mzip">Zip Code</label> &nbsp;
  <input name="mzip" type="text" id="mzip" size="5" maxlength="50" value="<?php if (isset($_POST['mzip'])) echo $_REQUEST['mzip']; ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><label for="trade">Trade</label></td>
    <td colspan="3"><input name="trade" type="text" id="trade" size="88" maxlength="100" value="<?php if (isset($_POST['trade'])) echo $_REQUEST['trade']; ?>" /></td>
  </tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="cphone">Phone</label></td>
    <td colspan="3"><input name="cphone" type="text" id="cphone" size="30" maxlength="15" value="<?php if (isset($_POST['cphone'])) echo $_REQUEST['cphone']; ?>" /></td>
    </tr>
    <tr>
    <td class="lbl"><label for="cmobile">Mobile</label></td>
    <td><input name="cmobile" type="text" id="cmobile" size="30" maxlength="15" value="<?php if (isset($_POST['cmobile'])) echo $_REQUEST['cmobile']; ?>" /></td>
    <td class="lbl"><label for="cfax">Fax</label></td>
    <td><input name="cfax" type="text" id="cfax" size="30" maxlength="15" value="<?php if (isset($_POST['cfax'])) echo $_REQUEST['cfax']; ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="cemail">Email</label></td>
    <td colspan="3"><input name="cemail" type="text" id="cemail" size="88" maxlength="100" value="<?php if (isset($_POST['cemail'])) echo $_REQUEST['cemail']; ?>" /></td>
  </tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="uname">Username</label></td>
    <td colspan="3"><input name="uname" type="text" id="uname" size="35" maxlength="75" value="<?php if (isset($_POST['uname'])) echo $_REQUEST['uname']; ?>" /></td>
  </tr>
</table>
    <p>Do you meet any of the following: <span class="note">(check all that apply)</span><br />
      <input name="s3" type="checkbox" id="s3"<?php if (isset($_POST['s3'])) echo ' checked="checked"'; ?> />
      <label for="s3">Section 3 Certified</label>
      &nbsp;&nbsp;&nbsp;
      <input name="mbe" type="checkbox" id="mbe"<?php if (isset($_POST['mbe'])) echo ' checked="checked"'; ?> />
      <label for="mbe"><acronym title="Minority Business Enterprise">MBE</acronym></label>
      &nbsp;&nbsp;&nbsp;
    <input name="wbe" type="checkbox" id="wbe"<?php if (isset($_POST['wbe'])) echo ' checked="checked"'; ?> />
    <label for="wbe"><acronym title="Woman Business Enterprise">WBE</acronym></label>
    &nbsp;&nbsp;&nbsp;
    <input name="other" type="checkbox" id="other"<?php if (isset($_POST['other'])) echo ' checked="checked"'; ?> />
    <label for="other">Other</label>
    <span class="note">- please specify</span> <input name="ocomment" type="text" size="25" maxlength="150" class="type" value="<?php if (isset($_POST['ocomment'])) echo $_REQUEST['ocomment']; ?>" /></p>
    <p class="btns">
      <input type="submit" name="submit" id="submit" value="Submit" class="btn" />
      <?php if (isset($_POST['MM_insert'])) { ?>
       <a href="?<?php echo $_SERVER['QUERY_STRING']; ?>" class="btn"><span>Reset</span></a>
	  <?php } else { ?>
       <input name="reset" type="reset" class="btn" id="reset" value="Reset" />
      <?php } //endif POST ?>
      <?php if ($id != '') { ?> 
       <a href="/rfp/admin/subusers.php" class="btn"><span>Cancel</span></a>
      <?php } //endif ?>
    </p>
</fieldset>
<input type="hidden" name="MM_insert" value="demo" />
</form> 
</div><!-- end sign-up form -->
<?php } //end sign-up check ?>
</div>
</div>
<?php require('../inc/footer.php'); ?>
</div>
</body>
</html>
