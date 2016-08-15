<?php require_once('../../Connections/adminConn.php'); ?>
<?php //determine pid
if (isset($_REQUEST['pid'])) {
	$pid = $_REQUEST['pid'];
} else {
	$pid = '';
}
?>
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
$MM_authorizedUsers = "1,2";
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "nstate")) {
	// validate input
	$err = "";
	if ( empty($_POST['state']) ) $err .= "<strong>State Name</strong> requires a value<br />";
	if ( empty($_POST['sabbr']) ) $err .= "<strong>State Abbr</strong> requires a value<br />";
	
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsDupe = "SELECT * FROM vt_state ORDER BY stateName ASC";
	$rsDupe = mysql_query($query_rsDupe, $adminConn) or die(mysql_error());
	$row_rsDupe = mysql_fetch_assoc($rsDupe);
	$totalRows_rsDupe = mysql_num_rows($rsDupe);mysql_select_db($database_adminConn, $adminConn);
	do {
		if ( strtolower($row_rsDupe['stateName']) == strtolower($_POST['state']) ) $err .= "<Strong>State Name (".$_POST['state'].")</strong> already exists<br />";
		if ( strtolower($row_rsDupe['stateAbbr']) == strtolower($_POST['sabbr']) ) $err .= "<Strong>State Abbr (".$_POST['sabbr'].")</strong> already exists<br />";
	} while ($row_rsDupe = mysql_fetch_assoc($rsDupe));
	mysql_free_result($rsDupe);
	
	if ($err == "") { // no errors, input into database
	  $insertSQL = sprintf("INSERT INTO vt_state (stateName, stateAbbr) VALUES (%s, %s)",
						   GetSQLValueString($_POST['state'], "text"),
						   GetSQLValueString($_POST['sabbr'], "text"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($insertSQL, $adminConn) or die(mysql_error());
	
	  $insertGoTo = "vt_projectstate.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
	  }
	  header(sprintf("Location: %s", $insertGoTo));
	}
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "ustate")) {
	// cancel
	if (isset($_POST['cancel']) && $_POST['cancel']=='Cancel') {
		header('Location: vt_projectstate.php');
	}
	
	// validate input
	$err = "";
	if ( empty($_POST['state']) ) $err .= "<strong>State Name</strong> requires a value";
	if ( empty($_POST['sabbr']) ) $err .= "<strong>State Abbr</strong> requires a value";

	mysql_select_db($database_adminConn, $adminConn);
	$query_rsDupe = "SELECT * FROM vt_state ORDER BY stateName ASC";
	$rsDupe = mysql_query($query_rsDupe, $adminConn) or die(mysql_error());
	$row_rsDupe = mysql_fetch_assoc($rsDupe);
	$totalRows_rsDupe = mysql_num_rows($rsDupe);mysql_select_db($database_adminConn, $adminConn);
	do {
		if ( ($_POST['sid']!=$row_rsDupe['id']) && (strtolower($row_rsDupe['stateName']) == strtolower($_POST['state'])) ) $err .= "<Strong>State Name (".$_POST['state'].")</strong> already exists<br />";
		if ( ($_POST['sid']!=$row_rsDupe['id']) && (strtolower($row_rsDupe['stateAbbr']) == strtolower($_POST['sabbr'])) ) $err .= "<Strong>State Abbr (".$_POST['sabbr'].")</strong> already exists<br />";
	} while ($row_rsDupe = mysql_fetch_assoc($rsDupe));
	mysql_free_result($rsDupe);
	
	if ($err == "") { // no errors, input into database
	  $updateSQL = sprintf("UPDATE vt_state SET stateName=%s, stateAbbr=%s WHERE id=%s",
                       GetSQLValueString($_POST['state'], "text"),
                       GetSQLValueString($_POST['sabbr'], "text"),
                       GetSQLValueString($_POST['sid'], "int"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
	
	  $updateGoTo = "vt_projectstate.php";
	  /*if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
	  }*/
	  header(sprintf("Location: %s", $updateGoTo));
	}
}

if (($pid=="del")) {
	  $deleteSQL = sprintf("DELETE FROM vt_state WHERE id=%s",
                       GetSQLValueString($_REQUEST['id'], "int"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($deleteSQL, $adminConn) or die(mysql_error());
	
	  $deleteGoTo = "vt_projectstate.php";
	  /*if (isset($_SERVER['QUERY_STRING'])) {
		$deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
		$deleteGoTo .= $_SERVER['QUERY_STRING'];
	  }*/
	  header(sprintf("Location: %s", $deleteGoTo));
}

mysql_select_db($database_adminConn, $adminConn);
$query_rsState = "SELECT * FROM vt_state ORDER BY stateName ASC";
$rsState = mysql_query($query_rsState, $adminConn) or die(mysql_error());
$row_rsState = mysql_fetch_assoc($rsState);
$totalRows_rsState = mysql_num_rows($rsState);mysql_select_db($database_adminConn, $adminConn);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="605" />
<title>RFP | Administration: Project States</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../inc/popup.js"></script>
<style type="text/css">
/*@import url("../../inc/default.css");*/
@import url("../../inc/child.css");
@import url("../../rfp/rfp.css");
#plist td { vertical-align:middle; }
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
<div id="plist">
<?php if ( $err <> "" ) echo '<p class="err">'.$err.'</p>'; ?>
<table border="0" cellpadding="4" cellspacing="1" id="plist">
<caption>Project State(s) / Location</caption>
<thead><tr>
<th>State Name</th>
<th>State Abbr</th>
<th>&nbsp;</th>
</tr></thead>
<tbody>
<?php do { ?>
<?php if (($pid == 'edit') && ((isset($_REQUEST['id'])) && ($row_rsState['id'] == $_REQUEST['id']))) { ?>
<form id="ustate" name="ustate" method="POST" action="<?php echo $editFormAction; ?>">
<tr>
<td class="t1"><input name="state" type="text" size="35" maxlength="45" value="<?php echo (isset($_POST['state'])) ? $_POST['state'] : $row_rsState['stateName']; ?>" /></td>
<td class="t2"><input name="sabbr" type="text" size="5" maxlength="2" value="<?php echo (isset($_POST['sabbr'])) ? $_POST['sabbr'] : $row_rsState['stateAbbr']; ?>" /></td>
<td class="t3"><input name="submit" type="submit" value="Update" class="btn" /> <input name="cancel" type="submit" value="Cancel" class="btn" /><input type="hidden" name="MM_update" value="ustate" /><input type="hidden" name="sid" value="<?php echo $row_rsState['id']; ?>" /></td>
</tr>
</form>
<?php } elseif ($totalRows_rsState <> 0) { ?>
<tr>
<td class="t1"><?php echo $row_rsState['stateName']; ?></td>
<td class="t2"><?php echo $row_rsState['stateAbbr']; ?></td>
<td class="t3">
<?php mysql_select_db($database_adminConn, $adminConn);
$query_rsCount = "SELECT COUNT(projState) AS numState FROM projects WHERE projState='".$row_rsState['stateName']."'";
$rsCount = mysql_query($query_rsCount, $adminConn) or die(mysql_error());
$row_rsCount = mysql_fetch_assoc($rsCount);
$totalRows_rsCount = mysql_num_rows($rsCount);mysql_select_db($database_adminConn, $adminConn); ?>
<a href="?id=<?php echo $row_rsState['id']; ?>&pid=edit" class="btn">edit</a> <?php if ($row_rsCount['numState']==0) { echo '<a href="?id='.$row_rsState['id'].'&pid=del" class="btn">delete</a>'; } ?>
<?php mysql_free_result($rsCount); ?>
</td>
</tr>
<?php } //endif ?>
<?php } while ($row_rsState = mysql_fetch_assoc($rsState)); ?>
<form id="nstate" name="nstate" method="POST" action="<?php echo $editFormAction; ?>">
<tr>
<td class="t1"><input name="state" type="text" size="35" maxlength="45" /></td>
<td class="t2"><input name="sabbr" type="text" size="5" maxlength="2" /></td>
<td class="t3"><input name="submit" type="submit" value="Add New State" class="btn" /><input type="hidden" name="MM_insert" value="nstate" /></td>
</tr>
</form>
</tbody>
</table>
</div>
</div>

<?php require('../../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsState);
?>
