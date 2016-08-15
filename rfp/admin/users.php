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

mysql_select_db($database_adminConn, $adminConn);
$query_rsUsers = "SELECT users.id, firstName, lastName, username, users.admLevel, admType FROM users, vt_admin WHERE users.admLevel=vt_admin.admLevel and username <> 'dummy' ORDER BY lastName, firstName ASC";
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
@import url("../../rfp/rfp.css");
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
<div id="ulist">
<table border="0" cellpadding="4" cellspacing="1" summary="add/modify users" id="ulist">
<caption>Add/Modify Users</caption>
<tr>
<th>User Information</th>
<th>Login</th>
<th>Access Level</th>
<th>&nbsp;</th>
</tr>
<?php do { ?>
  <tr>
    <td class="t1"><?php echo $row_rsUsers['lastName']; ?>, <?php echo $row_rsUsers['firstName']; ?></td>
    <td class="t2"><?php echo $row_rsUsers['username']; ?></td>
	<td class="t3"><?php echo $row_rsUsers['admType']; ?></td>
    <td class="t4">
      <?php if ($_SESSION['rfp_adm_MM_UserGroup']==1 || ($_SESSION['rfp_adm_MM_UserGroup']==2 && $row_rsUsers['admLevel']!=1)) { ?>
      <a href="user_edit.php?func=edit&id=<?php echo $row_rsUsers['id']; ?>" class="btn">edit</a> <a href="user_edit.php?func=del&id=<?php echo $row_rsUsers['id']; ?>" class="btn">delete</a>
      <?php } ?>
    </td>
  </tr>
  <?php } while ($row_rsUsers = mysql_fetch_assoc($rsUsers)); ?>
</table>
<p><a href="user_edit.php" class="btn"><span>Add New User</span></a></p>
</div>
</div>

<?php require('../../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsUsers);
?>
