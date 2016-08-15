<?php require_once('../../Connections/adminConn.php'); ?>
<?php
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
$MM_authorizedUsers = "1,2,3";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) { 
  // For security, start by assuming the visitor is NOT authorized. 
  $isValid = False; 

  // When a visitor has logged into this site, the Session variable rfp_adm_MM_Username set equal to their username. 
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
    if (($strUsers == "") && false) { 
      $isValid = true; 
    } 
  } 
  return $isValid; 
}

$MM_restrictGoTo = "denied.php";
if (!((isset($_SESSION['rfp_adm_MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['rfp_adm_MM_Username'], $_SESSION['rfp_adm_MM_UserGroup'])))) {   
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

// validate input
if ( isset($_POST["MM_insert"]) || isset($_POST['MM_update']) ) {
	$err="";
	if ( empty($_POST['lname']) ) $err .= "<li><strong>Last Name</strong> requires a value</li>";
	if ( empty($_POST['fname']) ) $err .= "<li><strong>First Name</strong> requires a value</li>";
	if ( empty($_POST['uname']) ) {
		$err .= "<li><strong>Login Name</strong> requires a value</li>";
	} else {
		mysql_select_db($database_adminConn, $adminConn);
		if (isset($_POST['MM_update'])) {
			$query_rsUname = sprintf("SELECT username FROM users WHERE username = %s AND id != %s", 
				GetSQLValueString($_POST['uname'], "text"),
				GetSQLValueString($_POST['id'], "int"));
		} else {
			$query_rsUname = sprintf("SELECT username FROM users WHERE username = %s", 
				GetSQLValueString($_POST['uname'], "text"));
		}
		$rsUname = mysql_query($query_rsUname, $adminConn) or die(mysql_error());
		$row_rsUname = mysql_fetch_assoc($rsUname);
		$totalRows_rsUname = mysql_num_rows($rsUname);
		if ($totalRows_rsUname != 0) $err .= "<li><strong>Login Name</strong> requires a unique value</li>";
		mysql_free_result($rsUname);
	}
	if ( empty($_POST['password']) ) $err .= "<li><strong>Password</strong> requires a value</li>";
	if ( $_POST['password'] != $_POST['cpassword']) $err .= "<li><strong>Passwords</strong> do not match</li>";
	if ( empty($_POST['accesslvl']) ) $err .= "<li><strong>Access Level</strong> requires a value</li>";
}

	
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "nuser") && ($err == "")) {
	  $insertSQL = sprintf("INSERT INTO users (firstName, lastName, username, password, admLevel) VALUES (%s, %s, %s, %s, %s)",
						   GetSQLValueString($_POST['fname'], "text"),
						   GetSQLValueString($_POST['lname'], "text"),
						   GetSQLValueString($_POST['uname'], "text"),
						   GetSQLValueString(better_crypt($_POST['password']), "text"),
						   GetSQLValueString($_POST['accesslvl'], "int"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($insertSQL, $adminConn) or die(mysql_error());

	  $insertGoTo = "users.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
	  }
	  header(sprintf("Location: %s", $insertGoTo));
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "euser") && ($err == "")) {
  $updateSQL = sprintf("UPDATE users SET firstName=%s, lastName=%s, username=%s, password=%s, admLevel=%s WHERE id=%s",
                       GetSQLValueString($_POST['fname'], "text"),
                       GetSQLValueString($_POST['lname'], "text"),
                       GetSQLValueString($_POST['uname'], "text"),
                       GetSQLValueString(better_crypt($_POST['password']), "text"),
                       GetSQLValueString($_POST['accesslvl'], "int"),
                       GetSQLValueString($_POST['id'], "int"));

  mysql_select_db($database_adminConn, $adminConn);
  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());

  $updateGoTo = "users.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
    $updateGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $updateGoTo));
}

if ((isset($_POST['MM_delete'])) && ($_POST['MM_delete'] == "duser")) {
  $deleteSQL = sprintf("DELETE FROM users WHERE id=%s",
                       GetSQLValueString($_GET['id'], "int"));

  mysql_select_db($database_adminConn, $adminConn);
  $Result1 = mysql_query($deleteSQL, $adminConn) or die(mysql_error());

  $deleteGoTo = "users.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $deleteGoTo));
}

mysql_select_db($database_adminConn, $adminConn);
$query_rsAdmLvl = "SELECT admLevel, admType FROM vt_admin WHERE admLevel <> 0 ORDER BY admLevel ASC";
$rsAdmLvl = mysql_query($query_rsAdmLvl, $adminConn) or die(mysql_error());
$row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl);
$totalRows_rsAdmLvl = mysql_num_rows($rsAdmLvl);

$colname_rsUsers = "-1";
if (isset($_GET['id'])) {
  $colname_rsUsers = $_GET['id'];
}
mysql_select_db($database_adminConn, $adminConn);
$query_rsUsers = sprintf("SELECT * FROM users WHERE id = %s", GetSQLValueString($colname_rsUsers, "int"));
$rsUsers = mysql_query($query_rsUsers, $adminConn) or die(mysql_error());
$row_rsUsers = mysql_fetch_assoc($rsUsers);
$totalRows_rsUsers = mysql_num_rows($rsUsers);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="605" />
<title>RFP | Administration: Users</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../inc/popup.js"></script>
<style type="text/css">
/*@import url("../../inc/default.css");*/
@import url("../../inc/child.css");
@import url("../rfp.css");
fieldset { border-color: transparent; }
</style>
</head>

<body>
<div id="layout">
<?php require('../../inc/header.php'); ?>
<div id="content">
<div id="topslide" class="rfp">
<p><span></span></p>
</div>
<h2><span>RFP Administration</span></h2>
<?php require('admnav.php'); ?>
<?php require('admsubnav.php'); ?>
<div id="elist">
<?php if (strtolower($_REQUEST['func'])=="del") { //delete user ?>
<form action="" method="post" id="duser">
<fieldset>
<legend>Delete User</legend>
<p>Are you sure you want to delete <strong><?php echo $row_rsUsers['firstName']; ?> <?php echo $row_rsUsers['lastName']; ?> (<?php echo $row_rsUsers['username']; ?>)</strong>?</p>
<p class="btns"><input name="Submit" type="submit" value="Delete" class="btn" /> 
  <a href="users.php" class="btn"><span>Cancel</span></a></p>
  <input type="hidden" name="MM_delete" value="duser" />
  </fieldset>
</form>
<?php } else if (strtolower($_REQUEST['func'])=="edit") { //edit user ?>
<form name="euser" action="<?php echo $editFormAction; ?>" method="POST" id="euser">
<fieldset>
<legend>Modify User</legend>
<p><span class="req">*</span> <span class="note">Denotes a required field</span></p>
<?php
if ( $err != "" ) {
	echo '<div class="err"><p>User could not be modified due to the following:</p><ul>';
	echo $err;
	echo '</ul></div>';
}
?>
<table border="0" cellpadding="4" cellspacing="1" summary="modify users" id="elist">
<tr>
<td class="lbl"><label for="lname"><span class="req">*</span> Last Name</label></td>
<td><input name="lname" type="text" id="lname" value="<?php echo (isset($_POST['lname'])) ? $_REQUEST['lname'] : $row_rsUsers['lastName']; ?>" size="30" maxlength="25" /></td>
</tr>
<tr>
<td class="lbl"><label for="fname"><span class="req">*</span> First Name</label></td>
<td><input name="fname" type="text" id="fname" value="<?php echo (isset($_POST['fname'])) ? $_REQUEST['fname'] : $row_rsUsers['firstName']; ?>" size="30" maxlength="25" /></td>
</tr>
<tr>
  <td class="lbl"><label for="uname"><span class="req">*</span> Login Name</label></td>
  <td><input name="uname" type="text" id="uname" value="<?php echo (isset($_POST['uname'])) ? $_REQUEST['uname'] : $row_rsUsers['username']; ?>" size="25" maxlength="25" /></td>
</tr>
<tr>
<td class="lbl"><label for="password"><span class="req">*</span> Password</label></td>
<td><input name="password" type="password" id="password" value="<?php echo (isset($_POST['password'])) ? $_REQUEST['password'] : $row_rsUsers['password']; ?>" size="30" maxlength="45" /></td>
</tr>
<tr>
<td class="lbl"><label for="cpassword">Confirm Password</label></td>
<td>
  <input name="cpassword" type="password" id="cpassword" value="<?php echo (isset($_POST['cpassword'])) ? $_REQUEST['cpassword'] : $row_rsUsers['password']; ?>" size="30" maxlength="45" />
  </td>
</tr>
<?php if ($_SESSION['rfp_adm_MM_UserGroup']=='1' || $_SESSION['rfp_adm_MM_UserGroup']=='2') { // only Admin or Manager can change access level?>
<tr>
  <td class="lbl"><label for="accesslvl"> <span class="req">*</span> Access Level</label></td>
  <td><select name="accesslvl" id="accesslvl">
    <option value=""></option>
    <?php if ($_SESSION['rfp_adm_MM_UserGroup']=='1') { // Admin - show all acess levels ?>
	<?php
do {  
?>
    <option value="<?php echo $row_rsAdmLvl['admLevel']?>"<?php echo ( (isset($_POST['accesslvl']) && $_REQUEST['accesslvl']==$row_rsAdmLvl['admLevel']) || ($row_rsUsers['admLevel']==$row_rsAdmLvl['admLevel']) ? ' selected="selected"' : "" ); ?>><?php echo $row_rsAdmLvl['admType']?></option>
    <?php
} while ($row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl));
  $rows = mysql_num_rows($rsAdmLvl);
  if($rows > 0) {
      mysql_data_seek($rsAdmLvl, 0);
	  $row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl);
  }
?>

    <?php } else if ($_SESSION['rfp_adm_MM_UserGroup']=='2') { // rfpAdmin - hide Admin access level ?>
	<?php
do {  
	if ($row_rsAdmLvl['admLevel']!='1') {
?>
    <option value="<?php echo $row_rsAdmLvl['admLevel']?>"<?php echo ( (isset($_POST['accesslvl']) && $_REQUEST['accesslvl']==$row_rsAdmLvl['admLevel']) || ($row_rsUsers['admLevel']==$row_rsAdmLvl['admLevel']) ? ' selected="selected"' : "" ); ?>><?php echo $row_rsAdmLvl['admType']?></option>
    <?php
	}
} while ($row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl));
  $rows = mysql_num_rows($rsAdmLvl);
  if($rows > 0) {
      mysql_data_seek($rsAdmLvl, 0);
	  $row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl);
  }
?>
    <?php } else { // rfpUser - hide Admin & rfpAdmin access levels ?>
	<?php
do {  
	if ($row_rsAdmLvl['admLevel']!='1' && $row_rsAdmLvl['admLevel']=='2') {
?>
    <option value="<?php echo $row_rsAdmLvl['admLevel']?>"<?php echo ( (isset($_POST['accesslvl']) && $_REQUEST['accesslvl']==$row_rsAdmLvl['admLevel']) || ($row_rsUsers['admLevel']==$row_rsAdmLvl['admLevel']) ? ' selected="selected"' : "" ); ?>><?php echo $row_rsAdmLvl['admType']?></option>
    <?php
	}
} while ($row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl));
  $rows = mysql_num_rows($rsAdmLvl);
  if($rows > 0) {
      mysql_data_seek($rsAdmLvl, 0);
	  $row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl);
  }
?>
    <?php } // end access levels ?>
	
	  </select></td>
</tr>
<?php } else { //access level stays the same
   echo '<input type="hidden" name="accesslvl" value="'.$row_rsUsers['admLevel'].'" />';
} ?>
</table>
<p class="btns"><input name="Submit" type="submit" value="Update" class="btn" /> <a href="users.php" class="btn"><span>Cancel</span></a>
  <input name="id" type="hidden" id="id" value="<?php echo $row_rsUsers['id']; ?>" />
</p>
</fieldset>
<input type="hidden" name="MM_update" value="euser" />
</form>
<?php } else { //display "Add New" ?>
<form name="nuser" action="<?php echo $editFormAction; ?>" method="POST" id="nuser">
<fieldset>
<legend>Add New User</legend>
<p><span class="req">*</span> <span class="note">Denotes a required field</span></p>
<?php
if ( $err != "" ) {
	echo '<div class="err"><p>New user could not be added due to the following:</p><ul>';
	echo $err;
	echo '</ul></div>';
}
?>
<table border="0" cellpadding="4" cellspacing="1" summary="add/modify users" id="elist">
<tr>
<td class="lbl"><label for="lname"><span class="req">*</span> Last Name</label></td>
<td><input name="lname" type="text" id="lname" size="30" maxlength="25" value="<?php echo (!empty($_POST['lname']) ? $_REQUEST['lname'] : ""); ?>" /></td>
</tr>
<tr>
<td class="lbl"><span class="req">*</span> <label for="fname">First Name</label></td>
<td><input name="fname" type="text" id="fname" size="30" maxlength="25" value="<?php echo (!empty($_POST['fname']) ? $_REQUEST['fname'] : ""); ?>" /></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="uname">Login Name</label></td>
  <td><input name="uname" type="text" id="uname" size="25" maxlength="25" value="<?php echo (!empty($_POST['uname']) ? $_REQUEST['uname'] : ""); ?>" /></td>
</tr>
<tr>
<td class="lbl"><span class="req">*</span> <label for="password">Password</label></td>
<td><input name="password" type="password" id="password" size="30" maxlength="45" value="<?php echo (!empty($_POST['password']) ? $_REQUEST['password'] : ""); ?>" /></td>
</tr>
<tr>
<td class="lbl"><label for="cpassword">Confirm Password</label></td>
<td>
  <input name="cpassword" type="password" id="cpassword" size="30" maxlength="45" value="<?php echo (!empty($_POST['cpassword']) ? $_REQUEST['cpassword'] : ""); ?>" />
  </td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="accesslvl">Access Level</label></td>
  <td><select name="accesslvl" id="accesslvl">
    <option value=""></option>
    <?php if ($_SESSION['rfp_adm_MM_UserGroup']=='1') { // Admin - show all acess levels ?>
	<?php
do {  
?>
    <option value="<?php echo $row_rsAdmLvl['admLevel']?>"<?php echo ( $_POST['accesslvl']==$row_rsAdmLvl['admLevel'] ? ' selected="selected"' : "" ); ?>><?php echo $row_rsAdmLvl['admType']?></option>
    <?php
} while ($row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl));
  $rows = mysql_num_rows($rsAdmLvl);
  if($rows > 0) {
      mysql_data_seek($rsAdmLvl, 0);
	  $row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl);
  }
?>

    <?php } else if ($_SESSION['rfp_adm_MM_UserGroup']=='2') { // rfpAdmin - hide Admin access level ?>
	<?php
do {  
	if ($row_rsAdmLvl['admLevel']!='1') {
?>
    <option value="<?php echo $row_rsAdmLvl['admLevel']?>"<?php echo ( $_POST['accesslvl']==$row_rsAdmLvl['admLevel'] ? ' selected="selected"' : "" ); ?>><?php echo $row_rsAdmLvl['admType']?></option>
    <?php
	}
} while ($row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl));
  $rows = mysql_num_rows($rsAdmLvl);
  if($rows > 0) {
      mysql_data_seek($rsAdmLvl, 0);
	  $row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl);
  }
?>
    <?php } else { // rfpUser - hide Admin & rfpAdmin access levels ?>
	<?php
do {  
	if ($row_rsAdmLvl['admLevel']!='1' && $row_rsAdmLvl['admLevel']!='2') {
?>
    <option value="<?php echo $row_rsAdmLvl['admLevel']?>"<?php echo ( $_POST['accesslvl']==$row_rsAdmLvl['admLevel'] ? ' selected="selected"' : "" ); ?>><?php echo $row_rsAdmLvl['admType']?></option>
    <?php
	}
} while ($row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl));
  $rows = mysql_num_rows($rsAdmLvl);
  if($rows > 0) {
      mysql_data_seek($rsAdmLvl, 0);
	  $row_rsAdmLvl = mysql_fetch_assoc($rsAdmLvl);
  }
?>
    <?php } // end access levels ?>
	
	  </select></td>
</tr>
</table>
<p class="btns"><input name="Submit" type="submit" value="Submit" class="btn" /> <a href="users.php" class="btn"><span>Cancel</span></a></p>
</fieldset>
<input type="hidden" name="MM_insert" value="nuser" />
</form>
<?php } //end function check ?>
</div>
</div>

<?php require('../../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsAdmLvl);

mysql_free_result($rsUsers);
?>
