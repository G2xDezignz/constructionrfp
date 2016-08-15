<?php require_once('../Connections/adminConn.php'); ?>
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
?>
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) { // If session is not already created, start one
	session_start();
	// set timeout period (in seconds)
	$inactive = 600; 
	// check to see if $_SESSION['timeout'] is set
	if ( isset($_SESSION['timeout']) ) {
		$session_life = time() - $_SESSION['timeout'];
		if ( $session_life > $inactive ) {
			session_destroy();
			header("Location: /");
		}
	}
	$_SESSION['timeout'] = time();
}

// *** Redirect if session already in existance 
if ($_SESSION["rfp_MM_Username"]!="") {
	header("Location: contract.php");
	exit;
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['cUsername'])) {
  $loginUsername=$_POST['cUsername'];
  $password=$_POST['cPassword'];
  $MM_fldUserAuthorization = "";
  $MM_redirectLoginSuccess = "contract.php";
  $MM_redirectLoginFailed = "denied.php";
  $MM_redirecttoReferrer = false;
  mysql_select_db($database_adminConn, $adminConn);
  
  $LoginRS__query=sprintf("SELECT id, username, password FROM demographics WHERE username=%s",
    GetSQLValueString($loginUsername, "text")); 
   
  $LoginRS = mysql_query($LoginRS__query, $adminConn) or die(mysql_error());
  $row_LoginRS = mysql_fetch_assoc($LoginRS);
  $loginFoundUser = mysql_num_rows($LoginRS);
  if ($loginFoundUser) {
	//check password
	$password_hash = mysql_result($LoginRS,0,'password');
	if(crypt($password, $password_hash) == $password_hash) {
		// password is correct
	} else {
    	header("Location: ". $MM_redirectLoginFailed );
		exit;
	}
    
	$loginStrGroup = "";
    
	if (PHP_VERSION >= 5.1) {session_regenerate_id(true);} else {session_regenerate_id();}
    //declare two session variables and assign them
    $_SESSION['rfp_MM_Username'] = $loginUsername;
    $_SESSION['rfp_MM_UserGroup'] = $loginStrGroup;	 
	$_SESSION['profileID'] = $row_LoginRS['id'];
	$_SESSION['project_id'] = $_POST['id'];  

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RFP | Contractor Login</title>
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
<form action="<?php echo $loginFormAction; ?>" method="POST" id="clogin">
<fieldset>
<legend>Login</legend>
<p>Contractors, subcontractors, and/or suppliers with login privileges may access their contracts or view current jobsite details here. Simply enter your username and password below to begin or <a href="apply.php">register</a>.</p>
<table border="0" cellspacing="1" cellpadding="4" summary="login form for authorized contractors">
<tr>
<td class="lbl"><label for="cUsername">Username</label></td>
<td><input name="cUsername" type="text" id="cUsername" size="25" maxlength="75" /></td>
</tr>
<tr>
<td class="lbl"><label for="cPassword">Password</label></td>
<td><input name="cPassword" type="password" id="cPassword" size="25" maxlength="45" /></td>
</tr>
</table>
<input type="hidden" id="id" name="id" value="<?php if (isset($_REQUEST['id'])) echo $_REQUEST['id']; ?>" />
<p style="margin:5px 0 0 210px;"><input name="submit" type="submit" class="btn" id="submit" value="Login" /></p>
</fieldset>
</form>
<p style="margin-left:115px; font-size:11px;"><a href="password.php">Forgotten username or password?</a></p>
<div class="warn">
<p>WARNING: This section of the Construction RFP web site is for authorized personnel only.<br />
All violators will be prosecuted to the full extent of the law.</p>
</div>
</div>
</div>
</div>
<?php require('../inc/footer.php'); ?>
</div>
</body>
</html>