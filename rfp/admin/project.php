<?php require_once('../../Connections/adminConn.php'); ?>
<?php
if (isset($_REQUEST['func'])) {
	$func = strtolower($_REQUEST['func']);
} else {
	$func = '';
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
$MM_authorizedUsers = "1,2,3,4";
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

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "uProject")) {
	//validate input
	$err = "";
	if ( empty($_POST['pname']) ) $err .= "<li><strong>Project Name</strong> requires a value</li>";
	if ( empty($_POST['pcity']) ) $err .= "<li><strong>City</strong> requires a value</li>";
	if ( empty($_POST['psummary']) ) $err .= "<li><strong>Summary</strong> requires a value</li>";
	if ( empty($_POST['pdetail']) ) $err .= "<li><strong>Detail</strong> requires a value</li>";
	if ( empty($_POST['pclose']) ) $err .= "<li><strong>Proposal Due Date</strong> requires a value</li>";

	if ($err == "") {
  	$updateSQL = sprintf("UPDATE projects SET projName=%s, projAddress=%s, projState=%s, projCity=%s, projZip=%s, projSummary=%s, projDetail=%s, rfpInfo=%s, closeDate=%s, projStatus=%s WHERE id=%s",
                       GetSQLValueString($_POST['pname'], "text"),
                       GetSQLValueString($_POST['paddr'], "text"),
                       GetSQLValueString($_POST['pstate'], "text"),
                       GetSQLValueString($_POST['pcity'], "text"),
                       GetSQLValueString($_POST['pzip'], "text"),
                       GetSQLValueString($_POST['psummary'], "text"),
                       GetSQLValueString($_POST['pdetail'], "text"),
                       GetSQLValueString($_POST['rfpnotes'], "text"),
                       GetSQLValueString($_POST['pclose'].' '.date('G:i', strtotime($_POST['pcloset'])), "date"),
                       GetSQLValueString($_POST['status'], "text"),
                       GetSQLValueString($_POST['id'], "int"));

	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
	
	  $updateGoTo = "home.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
	  }
	  header(sprintf("Location: %s", $updateGoTo));
	}
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "nProject")) {
	//validate input
	$err = "";
	if ( empty($_POST['pname']) ) $err .= "<li><strong>Project Name</strong> requires a value</li>";
	if ( empty($_POST['pcity']) ) $err .= "<li><strong>City</strong> requires a value</li>";
	if ( empty($_POST['psummary']) ) $err .= "<li><strong>Summary</strong> requires a value</li>";
	if ( empty($_POST['pdetail']) ) $err .= "<li><strong>Detail</strong> requires a value</li>";
	if ( empty($_POST['pclose']) ) $err .= "<li><strong>Proposal Due Date</strong> requires a value</li>";

	if ($err == "") {
  	$insertSQL = sprintf("INSERT INTO projects (id, projName, projAddress, projState, projCity, projZip, projSummary, projDetail, rfpInfo, closeDate, projStatus) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['id'], "int"),
                       GetSQLValueString($_POST['pname'], "text"),
                       GetSQLValueString($_POST['paddr'], "text"),
                       GetSQLValueString($_POST['pstate'], "text"),
                       GetSQLValueString($_POST['pcity'], "text"),
                       GetSQLValueString($_POST['pzip'], "text"),
                       GetSQLValueString($_POST['psummary'], "text"),
                       GetSQLValueString($_POST['pdetail'], "text"),
                       GetSQLValueString($_POST['rfpnotes'], "text"),
                       GetSQLValueString($_POST['pclose'].' '.date('G:i', strtotime($_POST['pcloset'])), "date"),
                       GetSQLValueString($_POST['status'], "text"));

	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($insertSQL, $adminConn) or die(mysql_error());
	
	  $insertGoTo = "plans.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
		$insertGoTo .= "&pid=" . $_POST['id'];
	  } else {
		$insertGoTo .= "?pid=" . $_POST['id'];
	  }
	  header(sprintf("Location: %s", $insertGoTo));
	}
}

if ( (isset($_POST['dzip']) && $_POST['dzip']=='Download Selected') ) {
	// Prepare File 
	$file = tempnam("tmp", "zip"); 
	$zip = new ZipArchive(); 
	$zip->open($file, ZipArchive::OVERWRITE); 
	$path = '../files/plans/';
	
	foreach ($_POST as $item => $value) {
		if (($item != 'dzip') && ($item != 'checkAll')){
		  #echo $item . ': ' . $value . '<br />';
		  $value = $path . $value;
		  $zip->addFile($value, basename($value));
		}
	}
	#exit;
	
	// Close and send to users 
	$zip->close(); 
	header('Content-Type: application/zip'); 
	header('Content-Length: ' . filesize($file)); 
	header('Content-Disposition: attachment; filename="ProjectPlansSpecs.zip"'); 
	readfile($file); 
	unlink($file);  
}


	$colname_rsProject = '-1';
	if (isset($_REQUEST['id'])) {
	  $colname_rsProject = $_REQUEST['id'];
	}
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsProject = sprintf("SELECT * FROM projects WHERE id = %s", GetSQLValueString($colname_rsProject, "int"));
	$rsProject = mysql_query($query_rsProject, $adminConn) or die(mysql_error());
	$row_rsProject = mysql_fetch_assoc($rsProject);
	$totalRows_rsProject = mysql_num_rows($rsProject);
	
	$colname_rsProposals = "-1";
	if (isset($_REQUEST['id'])) {
	  $colname_rsProposals = $_REQUEST['id'];
	}
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsProposals = sprintf("SELECT count(appType) AS total FROM bids WHERE projectID = %s", GetSQLValueString($colname_rsProposals, "int"));
	$rsProposals = mysql_query($query_rsProposals, $adminConn) or die(mysql_error());
	$row_rsProposals = mysql_fetch_assoc($rsProposals);
	$totalRows_rsProposals = mysql_num_rows($rsProposals);
	
	$colname_rsPlans = "-1";
	if (isset($_GET['id'])) {
	  $colname_rsPlans = $_GET['id'];
	}
	mysql_select_db($database_adminConn, $adminConn);
	if ( (isset($_POST["MM_view"]) && $_POST["MM_view"]=='psview') ) {
		if ( $_POST["submit"]=='View all plans' ) {
			$query_rsPlans = sprintf("SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and projectID = %s ORDER BY sheet ASC, revisionDate DESC", GetSQLValueString($colname_rsPlans, "int"));
			$psView = 'all';
		} else {
			$query_rsPlans = sprintf("SELECT * FROM (SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and projectID = %s ORDER BY sheet ASC, revisionDate DESC) AS tbl GROUP BY sheet", GetSQLValueString($colname_rsPlans, "int"));
			$psView ='';
		}
		if ( isset($_POST["vptSelect"]) && $_POST["vptSelect"]<>0 ) {
			$vpt = $_POST["vptSelect"];
			if ($_POST["hview"]=='all') {
				$query_rsPlans = sprintf("SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and vt_plantype.id = %s and projectID = %s ORDER BY sheet ASC, revisionDate DESC", GetSQLValueString($vpt, "int"), GetSQLValueString($colname_rsPlans, "int"));
				$psView='all';
			} else {
				$query_rsPlans = sprintf("SELECT * FROM (SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and vt_plantype.id = %s and projectID = %s ORDER BY sheet ASC, revisionDate DESC) AS tbl GROUP BY sheet", GetSQLValueString($vpt, "int"), GetSQLValueString($colname_rsPlans, "int"));
				$psView='';
			}
		}
	} else {
		$query_rsPlans = sprintf("SELECT * FROM (SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and projectID = %s ORDER BY sheet ASC, revisionDate DESC) AS tbl GROUP BY sheet", GetSQLValueString($colname_rsPlans, "int"));
	}
	$rsPlans = mysql_query($query_rsPlans, $adminConn) or die(mysql_error());
	$row_rsPlans = mysql_fetch_assoc($rsPlans);
	$totalRows_rsPlans = mysql_num_rows($rsPlans);
	
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsState = "SELECT * FROM vt_state ORDER BY stateName ASC";
	$rsState = mysql_query($query_rsState, $adminConn) or die(mysql_error());
	$row_rsState = mysql_fetch_assoc($rsState);
	$totalRows_rsState = mysql_num_rows($rsState);

	$colname_rsPlanType = "-1";
	if (isset($_REQUEST['id'])) {
	  $colname_rsPlanType = $_REQUEST['id'];
	}
mysql_select_db($database_adminConn, $adminConn);
	$query_rsPlanType = sprintf("SELECT DISTINCT planTypeID, planType FROM planspecs, vt_plantype WHERE planTypeID=vt_plantype.id AND planspecs.projectID = %s ORDER BY planType ASC", GetSQLValueString($colname_rsPlanType, "int"));
	$rsPlanType = mysql_query($query_rsPlanType, $adminConn) or die(mysql_error());
	$row_rsPlanType = mysql_fetch_assoc($rsPlanType);
	$totalRows_rsPlanType = mysql_num_rows($rsPlanType);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="605" />
<title>RFP | Administration: Project Detail</title>
<script type="text/javascript" src="/inc/checkAll.js"></script>
<script src="../../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../inc/popup.js"></script>
<style type="text/css">
/*@import url("../../inc/default.css");*/
@import url("../../inc/child.css");
@import url("../../rfp/rfp.css");
#vproject label { font-weight:bold; }
form#planspecs table { margin-left:0; }
</style>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div id="layout">
<?php require('../../inc/header.php'); ?>
<div id="content">
<div id="topslide" class="rfp">
<p><span></span></p>
</div>
<h2><span>RFP Administration</span></h2>
<div id="project">
<h3><span>Project Information</span></h3>
<?php require('admnav.php'); ?>
<?php
	/* Update a project... */ 
	if ( (strtolower($_REQUEST['func'])=='edit') || (strtolower($_REQUEST['func'])=='view') ) {
?>
<?php if ( !(strcmp($row_rsProject['projStatus'],"C")) || $_SESSION['rfp_adm_MM_UserGroup']==4 || (strtolower($_REQUEST['func'])=='view') ) { // Project Complete or Field - view only ?>
<table border="0" cellspacing="1" cellpadding="4" summary="view project information" id="vproject">
<tr>
<td class="lbl"><label> Project Name</label></td>
<td colspan="3"><?php echo $row_rsProject['projName']; ?></td>
</tr>
<tr>
  <td class="lbl"><label> Address</label></td>
  <td colspan="3"><?php echo $row_rsProject['projAddress']; ?></td>
</tr>
<tr>
  <td class="lbl"><label> City</label></td>
  <td colspan="3"><?php echo $row_rsProject['projCity']; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     <label>State</label>&nbsp; <?php echo $row_rsProject['projState']; ?>
    &nbsp;&nbsp;&nbsp;&nbsp;
       <label>Zip</label>&nbsp;
       <?php echo $row_rsProject['projZip']; ?> </td>
</tr>
<tr>
  <td class="lbl"><label> Summary </label></td>
  <td colspan="3"> <?php echo $row_rsProject['projSummary']; ?> </td>
</tr>
<tr>
  <td class="lbl"><label> Detail </label></td>
  <td colspan="3"><?php echo $row_rsProject['projDetail']; ?> </td>
</tr>
<tr>
  <td class="lbl"><label> RFP Instructions</label></td>
  <td colspan="3"><?php echo $row_rsProject['rfpInfo']; ?></td>
</tr>
<tr>
  <td class="lbl"><label> Proposal Due Date</label></td>
  <td><?php echo date('Y/m/d', strtotime($row_rsProject['closeDate'])); ?></td>
  <td class="lbl"><label> Proposal Due Time </label></td>
  <td><?php echo date('g:i a', strtotime($row_rsProject['closeDate'])); ?></td>
</tr>
</table>
<?php } else { // edit ?>
<form name="uProject" id="uProject" action="<?php echo $editFormAction; ?>" method="POST">
<p><span class="req">*</span> <span class="note">Denotes a required field</span></p>
<?php
if ( $err != "" ) {
	echo '<div class="err"><p>Project not submitted due to the following:</p><ul>';
	echo $err;
	echo '</ul></div>';
}
?>
<table border="0" cellspacing="1" cellpadding="4" summary="edit project information">
<tr>
<td class="lbl"><span class="req">*</span> <label for="pname">Project Name</label></td>
<td colspan="3"><input name="pname" type="text" id="pname" value="<?php echo(isset($_POST['pname']) ? $_POST['pname'] : $row_rsProject['projName']); ?>" size="80" maxlength="100" /></td>
</tr>
<tr>
  <td class="lbl"><label for="paddr">Address</label></td>
  <td colspan="3"><input name="paddr" type="text" id="paddr" value="<?php echo(isset($_POST['paddr']) ? $_POST['paddr'] : $row_rsProject['projAddress']); ?>" size="80" maxlength="100" /></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="pcity">City</label></td>
  <td colspan="3"><input name="pcity" type="text" id="pcity" value="<?php echo(isset($_POST['pcity']) ? $_POST['pcity'] : $row_rsProject['projCity']); ?>" size="28" maxlength="45" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <span class="req">*</span> <label for="pstate">State</label> &nbsp;
    <select name="pstate" id="pstate">
      <?php
do {  
?>
      <option value="<?php echo $row_rsState['stateName']?>"<?php if (!(strcmp($row_rsState['stateName'], $row_rsProject['projState'])) || (isset($_POST['pstate']) && $_POST['pstate']==$row_rsState['stateName'])) {echo "selected=\"selected\"";} ?>><?php echo $row_rsState['stateAbbr']?></option>
      <?php
} while ($row_rsState = mysql_fetch_assoc($rsState));
  $rows = mysql_num_rows($rsState);
  if($rows > 0) {
      mysql_data_seek($rsState, 0);
	  $row_rsState = mysql_fetch_assoc($rsState);
  }
?>
    </select>    &nbsp;&nbsp;&nbsp;&nbsp;
      <label for="pzip">Zip</label> &nbsp;
      <input name="pzip" type="text" id="pzip" value="<?php echo $row_rsProject['projZip']; ?>" size="5" maxlength="15" /></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="psummary">Summary</label></td>
  <td colspan="3"><textarea name="psummary" cols="82" rows="2" id="psummary"><?php echo(isset($_POST['psummary']) ? $_POST['psummary'] : $row_rsProject['projSummary']); ?></textarea></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="pdetail">Detail</label></td>
  <td colspan="3"><textarea name="pdetail" cols="82" rows="4" id="pdetail"><?php echo(isset($_POST['pdetail']) ? $_POST['pdetail'] : $row_rsProject['projDetail']); ?></textarea></td>
</tr>
<tr>
  <td class="lbl"><label for="rfpnotes">RFP Instructions</label></td>
  <td colspan="3"><textarea name="rfpnotes" cols="82" rows="2" id="rfpnotes"><?php echo $row_rsProject['rfpInfo']; ?></textarea></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="pclose">Proposal Due Date</label></td>
  <td><span id="sprytextfieldDate">
    <input name="pclose" type="text" id="pclose" value="<?php echo(isset($_POST['pclose']) ? $_POST['pclose'] : date('Y/m/d', strtotime($row_rsProject['closeDate']))); ?>" maxlength="10" size="11" /> <span class="note">yyyy/mm/dd</span><br /><span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
  <td class="lbl"><label for="pcloset">Proposal Due Time</label></td>
  <td><span id="sprytextfieldTime">
  <input name="pcloset" type="text" id="pcloset" value="<?php echo(isset($_POST['pcloset']) ? $_POST['pcloset'] : date('h:i a', strtotime($row_rsProject['closeDate']))); ?>"maxlength="8" size="9" />
<span class="note">hh:mm AM/PM</span><br /><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
</tr>
<tr>
  <td class="lbl">Status</td>
  <td colspan="3">
  <input <?php if ( strcmp($row_rsProject['projStatus'],"S") == 0 ) { echo 'checked="checked"'; } ?> type="radio" name="status" id="ssetup" value="S" class="radio" /><label for="ssetup">Project Setup</label>&nbsp;&nbsp;
  <input <?php if (!(strcmp($row_rsProject['projStatus'],"O"))) {echo 'checked="checked"';} ?> type="radio" name="status" id="sopen" value="O" class="radio" /><label for="sopen">Open Proposal</label>&nbsp;&nbsp;
  <input <?php if (!(strcmp($row_rsProject['projStatus'],"W"))) {echo 'checked="checked"';} ?> type="radio" name="status" id="swip" value="W" class="radio" /><label for="swip">Project Construction</label>&nbsp;&nbsp;
 <?php if ($_SESSION['rfp_adm_MM_UserGroup']==1 || $_SESSION['rfp_adm_MM_UserGroup']==2) { ?>
  <input <?php if (!(strcmp($row_rsProject['projStatus'],"C"))) {echo 'checked="checked"';} ?> type="radio" name="status" id="sclose" value="C" class="radio" /><label for="sclose">Project Completed</label>
  <?php } ?>
  </td>
</tr>
</table>
<p class="btns">
  <input type="submit" name="submit" id="submit" value="Update" class="btn" /> <a href="home.php" class="btn"><span>Cancel</span></a> 
  <input name="id" type="hidden" id="id" value="<?php echo $row_rsProject['id']; ?>" />
</p>
<input type="hidden" name="MM_update" value="uProject" />
</form>
<?php } // end edit vs view ?>
<p>There <?php echo($row_rsProposals['total']==1 ? 'is' : 'are'); ?> currently <strong><?php echo $row_rsProposals['total']; ?></strong> <?php echo($row_rsProposals['total']==1 ? 'proposal' : 'proposals'); ?> on file for this project. 
<?php if ($row_rsProposals['total'] <> 0) { 
echo '<a href="proposals.php?pid='.$row_rsProject['id'].'&func='.$_REQUEST['func'].'" class="btn"><span>View all proposals</span></a>'; 
} else if ($_SESSION['rfp_adm_MM_UserGroup']<>4) { 
echo '<a href="proposals.php?pid='.$row_rsProject['id'].'" class="btn"><span>Add new proposals</span></a>'; 
} ?></p>
</div>
<div id="specs2">
<h3><span>Plans &amp; Specifications</span></h3>
<?php if ( strcmp($row_rsProject['projStatus'],"C") && $_SESSION['rfp_adm_MM_UserGroup']<>4 ) { ?>
<?php if ($totalRows_rsPlans > 0) { // Show if recordset not empty ?>
<p>[ <a href="export.php?efunc=planlist&id=<?php echo $_GET['id']; ?>">Export current Plan List</a> ]</p>
<?php } ?>
<p><a href="plans.php?pid=<?php echo $_GET['id']; ?>" class="btn"><span>Add New Plans &amp; Specs</span></a></p><?php } //end show Add New Plans button ?>
<?php if ($totalRows_rsPlans > 0) { // Show if recordset not empty ?>
<div id="psview">
<form name="psv" id="psv" action="<?php echo $editFormAction; ?>#psview" method="post">
<input type="hidden" name="MM_view" id="MM_view" value="psview" />
<input type="hidden" name="hfltr" id="hfltr" value="<?php echo(isset($vpt) ? $vpt : '');?>" />
<?php if ($psView=='all') { ?>
<p>Showing <span class="ptstatus">all</span> plans. <input name="submit" id="submit" type="submit" class="btn" value="View current plans" /></p>
<?php } else { ?>
<p>Showing <span class="ptstatus">current</span> plans. <input name="submit" id="submit" type="submit" class="btn" value="View all plans" /></p>
<?php } ?>
</form>
</div><!-- end psview -->
<div id="psfilter">
<form id="psf" name="psf" action="<?php echo $editFormAction; ?>#psview" method="post">
<p>Filter by Plan Type:
<select name="vptSelect" id="vptSelect" onchange="this.form.submit()">
  <option value="0">- all -</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsPlanType['planTypeID']?>"<?php if ($row_rsPlanType['planTypeID']==$_POST['vptSelect']) { echo(' selected="selected"'); } ?>><?php echo $row_rsPlanType['planType']; ?></option>
  <?php
} while ($row_rsPlanType = mysql_fetch_assoc($rsPlanType));
  $rows = mysql_num_rows($rsPlanType);
  if($rows > 0) {
      mysql_data_seek($rsPlanType, 0);
	  $row_rsPlanType = mysql_fetch_assoc($rsPlanType);
  }
?>
</select>
<input type="hidden" id="MM_view" name="MM_view" value="psview" />
<input type="hidden" id="hview" name="hview" value="<?php echo($psView); ?>" />
</p>
</form>
</div><!-- end psfilter -->
<form id="planspecs" name="specslist" action="<?php echo $editFormAction; ?>" method="post">
  <table border="0" cellspacing="1" cellpadding="4" summary="list of project plans and specifications">
    <thead>
      <tr>
        <th scope="col"><input type="checkbox" id="checkAll" name="checkAll" value="Check All" onclick="CheckAll(document.specslist);" title="Select/Deselect All" class="chkbx" /></th>
        <th scope="col">Sheet</th>
        <th scope="col">Title</th>
        <th scope="col">Plan Type</th>
        <th scope="col">Revision Date</th>
        <?php if ( !(strcmp($row_rsProject['projStatus'],"C")) || $_SESSION['rfp_adm_MM_UserGroup']!=4 && (strtolower($_REQUEST['func'])!='view') ) { ?><th></th><?php } ?>
        </tr>
    </thead>
    <tbody>
      <?php 
	  $idx = 0;
	  do { ?>
        <tr>
          <td class="t0"><input type="checkbox" id="zip_<?php echo $idx; ?>" name="zip_<?php echo $idx; ?>" value="<?php echo $row_rsPlans['filename']; ?>" onclick="CheckCheckAll(document.specslist);" class="chkbx" /></td>
          <td class="t1"><?php echo $row_rsPlans['sheet']; ?></td>
          <td class="t2"><a href="/rfp/files/plans/<?php echo $row_rsPlans['filename']; ?>" target="_blank"><?php echo $row_rsPlans['title']; ?></a></td>
          <td class="t3"><?php echo $row_rsPlans['planType']; ?></td>
          <td class="t4"><?php echo $row_rsPlans['revisionDate']; ?></td>
          <?php if ( !(strcmp($row_rsProject['projStatus'],"C")) || $_SESSION['rfp_adm_MM_UserGroup']!=4 && (strtolower($_REQUEST['func'])!='view') ) { ?>
          <td class="t5" style="white-space:nowrap"><a href="plans.php?func=edit&pid=<?php echo $_GET['id']; ?>&id=<?php echo $row_rsPlans['id']; ?>" class="btn"><span>edit</span></a> <a href="plans.php?func=del&pid=<?php echo $_GET['id']; ?>&id=<?php echo $row_rsPlans['id']; ?>" class="btn"><span>del</span></a></td>
          <?php } ?>
        </tr>
        <?php 
		$idx = $idx + 1;
		} while ($row_rsPlans = mysql_fetch_assoc($rsPlans)); ?>
    </tbody>
  </table>
    <div style="padding-top:10px;"><input id="dzip" name="dzip" type="submit" value="Download Selected" class="btn" /></div>
  </form>
  <?php 
	} else { //show if recordset is empty
		echo('<p>There are currently no plans or specifications available.</p>');
	} // end - Show if recordset not empty ?>
</div>
<?php 
	} else {  /* Add a new project */
	// set the project ID
	$date = getdate();
	$id = "$date[0]";
?>
<div id="newProject">
<form name="nProject" id="nProject" action="<?php echo $editFormAction; ?>" method="POST">
<p><span class="req">*</span> <span class="note">Denotes a required field</span></p>
<?php
if ( $err != "" ) {
	echo '<div class="err"><p>Project not submitted due to the following:</p><ul>';
	echo $err;
	echo '</ul></div>';
}
?>
<table border="0" cellspacing="1" cellpadding="4" summary="edit project information">
<tr>
<td class="lbl"><span class="req">*</span> <label for="pname">Project Name</label></td>
<td colspan="3"><input name="pname" type="text" id="pname" size="80" maxlength="100" value="<?php echo(isset($_POST['pname']) ? $_POST['pname'] : '' ); ?>" /></td>
</tr>
<tr>
  <td class="lbl"><label for="paddr">Address</label></td>
  <td colspan="3"><input name="paddr" type="text" id="paddr" size="80" maxlength="100" value="<?php echo(isset($_POST['paddr']) ? $_POST['paddr'] : '' ); ?>" /></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="pcity">City</label></td>
  <td colspan="3"><input name="pcity" type="text" id="pcity" size="28" maxlength="45" value="<?php echo(isset($_POST['pcity']) ? $_POST['pcity'] : '' ); ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <span class="req">*</span> <label for="pstate">State</label> &nbsp;
    <select name="pstate" id="pstate">
      <?php
do {  
?>
      <option value="<?php echo $row_rsState['stateName']?>"<?php echo(isset($_POST['pstate']) && $_POST['pstate']==$row_rsState['stateName'] ? ' selected="selected"' : ''); ?>><?php echo $row_rsState['stateAbbr']?></option>
      <?php
} while ($row_rsState = mysql_fetch_assoc($rsState));
  $rows = mysql_num_rows($rsState);
  if($rows > 0) {
      mysql_data_seek($rsState, 0);
	  $row_rsState = mysql_fetch_assoc($rsState);
  }
?>
    </select>    &nbsp;&nbsp;&nbsp;&nbsp;
      <label for="pzip">Zip</label> &nbsp;
      <input name="pzip" type="text" id="pzip" size="5" maxlength="15" /></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="psummary">Summary</label></td>
  <td colspan="3"><textarea name="psummary" cols="82" rows="2" id="psummary"><?php echo(isset($_POST['psummary']) ? $_POST['psummary'] : ''); ?></textarea></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="pdetail">Detail</label></td>
  <td colspan="3"><textarea name="pdetail" cols="82" rows="4" id="pdetail"><?php echo(isset($_POST['pdetail']) ? $_POST['pdetail'] : ''); ?></textarea></td>
</tr>
<tr>
  <td class="lbl"><label for="rfpnotes">RFP Instructions</label></td>
  <td colspan="3"><textarea name="rfpnotes" cols="82" rows="2" id="rfpnotes"></textarea></td>
</tr>
<tr>
  <td class="lbl"><span class="req">*</span> <label for="pclose">Proposal Due Date</label></td>
  <td><span id="sprytextfieldDate">
    <input name="pclose" type="text" id="pclose" value="<?php echo(isset($_POST['pclose']) ? $_POST['pclose'] : date('Y/m/d', strtotime($row_rsProject['closeDate']))); ?>" maxlength="10" size="11" /> <span class="note">yyyy/mm/dd</span><br /><span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
  <td class="lbl"><label for="pcloset">Proposal Due Time</label></td>
  <td><span id="sprytextfieldTime">
  <input name="pcloset" type="text" id="pcloset" value="<?php echo(isset($_POST['pcloset']) ? $_POST['pcloset'] : date('h:i a', strtotime($row_rsProject['closeDate']))); ?>" maxlength="8" size="9" />
<span class="note">hh:mm AM/PM</span><br /><span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
</tr>
<tr>
  <td class="lbl">Status</td>
  <td colspan="3">
  <input name="status" type="radio" class="radio" id="ssetup" value="S" checked="checked" /><label for="ssetup">Project Setup</label>&nbsp;&nbsp;
  <input name="status" type="radio" class="radio" id="sopen" value="O" /><label for="sopen">Open Proposal</label>&nbsp;&nbsp;
  <input name="status" type="radio" class="radio" id="swip" value="W" /><label for="swip">Project Construction</label>&nbsp;&nbsp;
  <input type="radio" name="status" id="sclose" value="C" class="radio" /><label for="sclose">Project Completed</label>
  </td>
</tr>
</table>
<p class="btns">
  <input type="submit" name="submit" id="submit" value="Submit" class="btn" /> <a href="home.php" class="btn"><span>Cancel</span></a> 
  <input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
</p>
<input type="hidden" name="MM_insert" value="nProject" />
</form>
<?php } ?>
</div>
</div>

<?php require('../../inc/footer.php'); ?>
</div>
<script type="text/javascript">
var sprytextfieldDate = new Spry.Widget.ValidationTextField("sprytextfieldDate", "date", {format:"yyyy/mm/dd"});
var sprytextfieldTime = new Spry.Widget.ValidationTextField("sprytextfieldTime", "time", {format:"hh:mm tt", isRequired:false});
</script>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsProject);

mysql_free_result($rsProposals);

mysql_free_result($rsPlans);

mysql_free_result($rsState);

mysql_free_result($rsPlanType);
?>
