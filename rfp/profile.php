<?php require_once('../Connections/adminConn.php'); ?>
<?php
//initialize the session
if (!isset($_SESSION)) {
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

// ** Logout the current user. **
$logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
if ((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING'] != "")){
  $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_GET['doLogout'])) &&($_GET['doLogout']=="true")){
  //to fully log out a visitor we need to clear the session varialbles
  $_SESSION['rfp_MM_Username'] = NULL;
  $_SESSION['rfp_MM_UserGroup'] = NULL;
  $_SESSION['PrevUrl'] = NULL;
  $_SESSION['project_id'] = NULL;   
  $_SESSION['profileID'] = NULL;  
  unset($_SESSION['rfp_MM_Username']);
  unset($_SESSION['rfp_MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
  unset($_SESSION['project_id']);
  unset($_SESSION['profileID']);
	
  $logoutGoTo = "/";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
$MM_authorizedUsers = "";
$MM_donotCheckaccess = "true";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable rfp_MM_Username set equal to their username. 
  // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
  if (!empty($UserName)) { 
    // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
    // Parse the strings into arrays. 
    $arrUsers = Explode(",", $strUsers); 
    $arrGroups = Explode(",", $strGroups); 
    if (in_array($UserName, $arrUsers)) { 
      $isValid = true; 
    } 
    // Or, you may restrict access to only certain users based on their username. 
    if (in_array($UserGroup, $arrGroups)) { 
      $isValid = true; 
    } 
    if (($strUsers == "") && true) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "denied.php";
if (!((isset($_SESSION['rfp_MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['rfp_MM_Username'], $_SESSION['rfp_MM_UserGroup']))) && !(isset($_SESSION['func']) && $_SESSION['func']='admin')) {   
  $MM_qsChar = "?";
  $MM_referrer = $_SERVER['PHP_SELF'];
  if (strpos($MM_restrictGoTo, "?")) $MM_qsChar = "&";
  if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) 
  $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
  $MM_restrictGoTo = $MM_restrictGoTo. $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
  header("Location: ". $MM_restrictGoTo); 
  exit;
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "profile")) {
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

	if ( $err == "" ) {
		//update database
	  $updateSQL = sprintf("UPDATE demographics SET FirstName=%s, LastName=%s, Title=%s, Email=%s, Address=%s, City=%s, `State`=%s, Zip=%s, Company=%s, Trade=%s, Phone=%s, Mobile=%s, Fax=%s, sect3=%s, mbe=%s, wbe=%s, other=%s WHERE id=%s",
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
						   GetSQLValueString($_POST['pid'], "int"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
	
	  $updateGoTo = "profile.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
		$updateGoTo .= '&proc=updProfile';
	  } else {
		$updateGoTo .= '?proc=updProfile';
	  }
	  header(sprintf("Location: %s", $updateGoTo));
	}
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "passwrd")) {
	//validate input
	$errp = "";
	if ( empty($_POST['npswd']) || empty($_POST['cpswd']) ) $errp .= "<li>You must enter a password and confirm</li>";
	if ( $_POST['npswd'] != $_POST['cpswd'] ) $errp .= "<li>The passwords do not match</li>";

	if ( $errp == "" ) {
		//update database
	  $updateSQL = sprintf("UPDATE demographics SET password=%s, passhint=%s WHERE id=%s",
						   GetSQLValueString(better_crypt($_POST['cpswd']), "text"),
						   GetSQLValueString($_POST['cpswd'], "text"),
						   GetSQLValueString($_POST['pid'], "int"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
	
	  $updateGoTo = "profile.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
		$updateGoTo .= '&proc=updPswd';
	  } else {
		$updateGoTo .= '?proc=updPswd';
	  }
	  header(sprintf("Location: %s", $updateGoTo));
	}
}

$colname_rsProfile = "-1";
if (isset($_SESSION['profileID'])) {
  $colname_rsProfile = $_SESSION['profileID'];
}
if (isset($_SESSION['func']) && $_SESSION['func']='admin') {
  $colname_rsProfile = $_REQUEST['id'];
}
if (isset($_GET['func']) && $_GET['func']=='del') {
mysql_select_db($database_adminConn, $adminConn);
$query_rsProfile = sprintf("UPDATE demographics AS d SET d.delete = 1 WHERE id = %s", GetSQLValueString($colname_rsProfile, "int"));
$rsProfile = mysql_query($query_rsProfile, $adminConn) or die(mysql_error());
$row_rsProfile = mysql_fetch_assoc($rsProfile);
$totalRows_rsProfile = mysql_num_rows($rsProfile);
mysql_free_result($rsProfile);
header("Location: admin/subusers.php");
exit;
} else {
mysql_select_db($database_adminConn, $adminConn);
$query_rsProfile = sprintf("SELECT * FROM demographics WHERE id = %s", GetSQLValueString($colname_rsProfile, "int"));
$rsProfile = mysql_query($query_rsProfile, $adminConn) or die(mysql_error());
$row_rsProfile = mysql_fetch_assoc($rsProfile);
$totalRows_rsProfile = mysql_num_rows($rsProfile);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="605" />
<title>RFP | Contractor Access: Project Detail</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/inc/popup.js"></script>
<style type="text/css">
/*@import url("/inc/default.css");*/
@import url("/inc/child.css");
@import url("rfp.css");
.logout { text-align: right; }
form#profile { width:695px; float:left; }
form#pswd, #activity { width:255px; float:right; }
form#pswd table { margin-left:2px; }
form#pswd table td.lbl { white-space:nowrap; }
span.uname { color:grey; font-weight:normal; font-size:12px; }
form#profile.view label { font-weight:bold; white-space:nowrap; }
#activity p { margin-left:10px; }
#activity p span { font-weight:bold; }
</style>
</head>

<body>
<div id="layout">
<?php require('../inc/header.php'); ?>
<div id="content">
<div id="topslide" class="rfp">
<p><span></span></p>
</div>
<h2><span>Contractor Access</span></h2>
<?php if ($_SESSION['func']=='admin') { ?>
<p class="logout"><a href="admin/subusers.php" class="btn">Sub Profile(s)</a> <a href="admin/home.php" class="btn">Project List</a> <a href="admin/logout.php" class="btn">Logout</a></p>
<?php } else { ?>
<p class="logout"><a href="contract.php" class="btn">Project Info</a> <a href="<?php echo $logoutAction ?>" class="btn">Logout</a></p>
<?php } ?>
<div id="profile">
<?php if ($_SESSION['rfp_adm_MM_UserGroup']==4) { //show read-only info ?>
<form id="profile" name="profile" class="view">
<fieldset>
<legend>Contractor Profile &nbsp;<span class="uname">[ Username: <?php echo $row_rsProfile['username']; ?> ]</span></legend>
<table border="0" cellspacing="1" cellpadding="4" summary="request for proposal form">
  <tr>
    <td class="lbl"> <label for="fname">First Name</label></td>
    <td style="min-width:95px;"><?php echo ($row_rsProfile['FirstName']); ?></td>
    <td class="lbl"> <label for="lname">Last Name</label></td>
    <td><?php echo ($row_rsProfile['LastName']); ?></td>
    </tr>
  <tr>
    <td class="lbl"><label for="ctitle">Title</label></td>
    <td><?php echo ($row_rsProfile['Title']); ?></td>
    <td class="lbl"><label for="cname">Company</label></td>
    <td><?php echo ($row_rsProfile['Company']); ?></td>
    </tr>
  <tr>
    <td class="lbl"> <label for="maddress">Address</label></td>
    <td colspan="3"><?php echo ($row_rsProfile['Address']); ?></td>
    </tr>
  <tr>
    <td class="lbl"> <label for="mcity">City</label></td>
    <td><?php echo ($row_rsProfile['City']); ?></td>
    <td class="lbl"> <label for="mstate">State</label></td>
<td><?php echo ($row_rsProfile['State']); ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      
 <label for="mzip">Zip Code</label> &nbsp;
  <?php echo ($row_rsProfile['Zip']); ?></td>
    </tr>
  <tr>
    <td class="lbl"><label for="trade">Trade</label></td>
    <td colspan="3"><?php echo ($row_rsProfile['Trade']); ?></td>
  </tr>
  <tr>
    <td class="lbl"> <label for="cphone">Phone</label></td>
    <td colspan="3"><?php echo ($row_rsProfile['Phone']); ?></td>
    </tr>
    <tr>
    <td class="lbl"><label for="cmobile">Mobile</label></td>
    <td><?php echo ($row_rsProfile['Mobile']); ?></td>
    <td class="lbl"><label for="cfax">Fax</label></td>
    <td><?php echo ($row_rsProfile['Fax']); ?></td>
    </tr>
  <tr>
  <tr>
    <td class="lbl"> <label for="cemail">Email</label></td>
    <td colspan="3"><?php echo ($row_rsProfile['Email']); ?></td>
  </tr>
</table>
    <p>
      [ <?php echo($row_rsProfile['sect3']==1 ? 'x' : '&nbsp;'); ?> ] <label for="s3">Section 3 Certified</label>
      &nbsp;&nbsp;&nbsp;
      [ <?php echo($row_rsProfile['mbe']==1 ? 'x' : '&nbsp;'); ?> ] <label for="mbe"><acronym title="Minority Business Enterprise">MBE</acronym></label>
      &nbsp;&nbsp;&nbsp;
    [ <?php echo($row_rsProfile['wbe']==1 ? 'x' : '&nbsp;'); ?> ] <label for="wbe"><acronym title="Woman Business Enterprise">WBE</acronym></label>
    &nbsp;&nbsp;&nbsp;
    [ <?php echo($row_rsProfile['other']!='' ? 'x' : '&nbsp;'); ?> ] <label for="other">Other</label>
    <span class="note"><?php echo ($row_rsProfile['other']<>'' ? '- '.$row_rsProfile['other'] : ''); ?></span></p>
</fieldset>
</form>
<?php } else { //show update form ?>
<form id="profile" name="profile" action="<?php echo $editFormAction; ?>" method="POST">
<fieldset>
<legend>Update Profile &nbsp;<span class="uname">[ Username: <?php echo $row_rsProfile['username']; ?> ]</span></legend>
<?php if ($_REQUEST['proc']=='updProfile') { ?>
<p style="font-weight:bold;color:blue">Profile updated</p>
<?php } //endif ?>
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
    <td><input name="fname" type="text" id="fname" size="30" maxlength="45" value="<?php echo (isset($_POST['fname']) ? $_REQUEST['fname'] : $row_rsProfile['FirstName']); ?>" /></td>
    <td class="lbl"><span class="req">*</span> <label for="lname">Last Name</label></td>
    <td><input name="lname" type="text" id="lname" size="35" maxlength="45" value="<?php echo (isset($_POST['lname']) ? $_REQUEST['lname'] : $row_rsProfile['LastName']); ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><label for="ctitle">Title</label></td>
    <td><input name="ctitle" type="text" id="ctitle" size="30" maxlength="100" value="<?php echo (isset($_POST['ctitle']) ? $_REQUEST['ctitle'] : $row_rsProfile['Title']); ?>" /></td>
    <td class="lbl"><label for="cname">Company</label></td>
    <td><input name="cname" type="text" id="cname" size="35" maxlength="100" value="<?php echo (isset($_POST['cname']) ? $_REQUEST['cname'] : $row_rsProfile['Company']); ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="maddress">Address</label></td>
    <td colspan="3"><input name="maddress" type="text" id="maddress" size="88" maxlength="100" value="<?php echo (isset($_POST['maddress']) ? $_REQUEST['maddress'] : $row_rsProfile['Address']); ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="mcity">City</label></td>
    <td><input name="mcity" type="text" id="mcity" size="30" maxlength="75" value="<?php echo (isset($_POST['mcity']) ? $_REQUEST['mcity'] : $row_rsProfile['City']); ?>" /></td>
    <td class="lbl"><span class="req">*</span> <label for="mstate">State</label></td>
<td><input name="mstate" type="text" id="mstate" size="8" maxlength="45" value="<?php echo (isset($_POST['mstate']) ? $_REQUEST['mstate'] : $row_rsProfile['State']); ?>" />&nbsp;&nbsp;      
<span class="req">*</span> <label for="mzip">Zip Code</label> &nbsp;
  <input name="mzip" type="text" id="mzip" size="5" maxlength="50" value="<?php echo (isset($_POST['mzip']) ? $_REQUEST['mzip'] : $row_rsProfile['Zip']); ?>" /></td>
    </tr>
  <tr>
    <td class="lbl"><label for="trade">Trade</label></td>
    <td colspan="3"><input name="trade" type="text" id="trade" size="88" maxlength="100" value="<?php echo (isset($_POST['trade']) ? $_REQUEST['trade'] : $row_rsProfile['Trade']); ?>" /></td>
  </tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="cphone">Phone</label></td>
    <td colspan="3"><input name="cphone" type="text" id="cphone" size="30" maxlength="15" value="<?php echo (isset($_POST['cphone']) ? $_REQUEST['cphone'] : $row_rsProfile['Phone']); ?>" /></td>
    </tr>
    <tr>
    <td class="lbl"><label for="cmobile">Mobile</label></td>
    <td><input name="cmobile" type="text" id="cmobile" size="30" maxlength="15" value="<?php echo (isset($_POST['cmobile']) ? $_REQUEST['cmobile'] : $row_rsProfile['Mobile']); ?>" /></td>
    <td class="lbl"><label for="cfax">Fax</label></td>
    <td><input name="cfax" type="text" id="cfax" size="30" maxlength="15" value="<?php echo (isset($_POST['cfax']) ? $_REQUEST['cfax'] : $row_rsProfile['Fax']); ?>" /></td>
    </tr>
  <tr>
  <tr>
    <td class="lbl"><span class="req">*</span> <label for="cemail">Email</label></td>
    <td colspan="3"><input name="cemail" type="text" id="cemail" size="88" maxlength="100" value="<?php echo (isset($_POST['cemail']) ? $_REQUEST['cemail'] : $row_rsProfile['Email']); ?>" /></td>
  </tr>
</table>
    <p>Do you meet any of the following: <span class="note">(check all that apply)</span><br />
      <input name="s3" type="checkbox" id="s3"<?php if (isset($_POST['s3']) || $row_rsProfile['sect3']==1) echo ' checked="checked"'; ?> />
      <label for="s3">Section 3 Certified</label>
      &nbsp;&nbsp;&nbsp;
      <input name="mbe" type="checkbox" id="mbe"<?php if (isset($_POST['mbe']) || $row_rsProfile['mbe']==1) echo ' checked="checked"'; ?> />
      <label for="mbe"><acronym title="Minority Business Enterprise">MBE</acronym></label>
      &nbsp;&nbsp;&nbsp;
    <input name="wbe" type="checkbox" id="wbe"<?php if (isset($_POST['wbe']) || $row_rsProfile['wbe']==1) echo ' checked="checked"'; ?> />
    <label for="wbe"><acronym title="Woman Business Enterprise">WBE</acronym></label>
    &nbsp;&nbsp;&nbsp;
    <input name="other" type="checkbox" id="other"<?php if (isset($_POST['other']) || $row_rsProfile['other']!='') echo ' checked="checked"'; ?> />
    <label for="other">Other</label>
    <span class="note">- please specify</span> <input name="ocomment" type="text" size="25" maxlength="150" class="type" value="<?php echo (isset($_POST['ocomment']) ? $_REQUEST['ocomment'] : $row_rsProfile['other']); ?>" /></p>
    <p class="btns">
      <input type="submit" name="submit" id="submit" value="Update" class="btn" /> 
      <?php if (isset($_SESSION['func']) && $_SESSION['func']='admin') { ?>
      <a href="admin/subusers.php" class="btn"><span>Cancel</span></a>
      <?php } else { ?>
      <a href="contract.php" class="btn"><span>Cancel</span></a>
      <?php } ?>
      <input type="hidden" name="pid" id="pid" value="<?php echo $row_rsProfile['id']; ?>" />
    </p>
</fieldset>
<input type="hidden" name="MM_update" value="profile" />
</form>
<?php } //end show profile info ?>

<?php if ( $_SESSION['rfp_adm_MM_UserGroup']<>4 ) { //show per AdmLevel ?>
<form id="pswd" name="pswd" method="POST" action="<?php echo $editFormAction; ?>">
<fieldset>
<legend>Change Password</legend>
<?php if ($_REQUEST['proc']=='updPswd') { ?>
<p style="font-weight:bold;color:blue">Password changed</p>
<?php } //endif ?>
<?php
if ( $errp != "" ) {
	echo '<div class="err"><p>There was an error:</p><ul>';
	echo $errp;
	echo '</ul></div>';
}
?>
<table border="0" cellspacing="1" cellpadding="4">
<tr>
<td class="lbl"><label for="npswd">New Password</label></td>
<td><input id="npswd" name="npswd" type="password" size="15" maxlength="45" value="<?php echo (isset($_POST['npswd']) ? $_POST['npswd'] : '');?>" /></td>
</tr>
<tr>
<td class="lbl"><label for="cpswd">Confirm Password</label></td>
<td><input id="cpswd" name="cpswd" type="password" size="15" maxlength="45" value="<?php echo (isset($_POST['cpswd']) ? $_POST['cpswd'] : '');?>" /></td>
</tr>
</table>
    <p class="btns">
      <input type="submit" name="submit" id="submit" value="Change" class="btn" />
      <?php if (isset($_SESSION['func']) && $_SESSION['func']='admin') { ?>
      <a href="admin/subusers.php" class="btn"><span>Cancel</span></a>
      <?php } else { ?>
      <a href="contract.php" class="btn"><span>Cancel</span></a>
      <?php } ?>
      <input type="hidden" name="pid" id="pid" value="<?php echo $row_rsProfile['id']; ?>" />
    </p>
</fieldset>
<input type="hidden" name="MM_update" value="passwrd" />
</form>
<?php } //show per AdmLevel ?>
</div>
<?php if (isset($_SESSION['func']) && isset($_SESSION['rfp_adm_MM_UserGroup'])) { //show last activity and count total proposals submitted ?>
<?php
mysql_select_db($database_adminConn, $adminConn);

$query_rsActivity = sprintf("SELECT UNIX_TIMESTAMP(submitDate) AS LastActivity FROM bids WHERE subID = %s ORDER BY submitDate DESC, id LIMIT 1", GetSQLValueString($colname_rsProfile, "int"));
$rsActivity = mysql_query($query_rsActivity, $adminConn) or die(mysql_error());
$row_rsActivity = mysql_fetch_assoc($rsActivity);
$totalRows_rsActivity = mysql_num_rows($rsActivity);

$query_rsTotal = sprintf("SELECT COUNT(id) AS TotalBids FROM bids WHERE subID = %s", GetSQLValueString($colname_rsProfile, "int"));
$rsTotal = mysql_query($query_rsTotal, $adminConn) or die(mysql_error());
$row_rsTotal = mysql_fetch_assoc($rsTotal);
$totalRows_rsTotal = mysql_num_rows($rsTotal);
?>
<div id="activity">
<p><span>Total Proposals Submitted:</span> <?php echo($row_rsTotal['TotalBids']); ?></p>
<p><span>Last Submitted:</span> <?php echo($row_rsActivity['LastActivity']<>'' ? date('m-d-Y g:ia', $row_rsActivity['LastActivity']) : '---'); ?></p>
<?php if ($row_rsTotal['TotalBids']<>0) { ?><p style="margin-left:7px;"><a href="admin/allsubprops.php?sid=<?php echo $colname_rsProfile; ?>" class="btn">View All Submitted Proposals</a></p><?php } ?>
</div>
<?php 
mysql_free_result($rsTotal);
mysql_free_result($rsActivity);
} //end activity 
?>
</div>
<?php require('../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsProfile);
?>
