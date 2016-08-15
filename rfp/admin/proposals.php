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

if (isset($_POST['MM_insert']) && $_POST['MM_insert'] == 'bstat' ) {
	switch ($_POST['submit']) {
		case 'Award':
			$updateSQL = sprintf("UPDATE bids AS b SET b.status='A' WHERE id=%s", GetSQLValueString($_POST['bidID'], "int"));
			break;
		case 'Tent.':
			$updateSQL = sprintf("UPDATE bids AS b SET b.status='T' WHERE id=%s", GetSQLValueString($_POST['bidID'], "int"));
			break;
		case 'Reject':
			$updateSQL = sprintf("UPDATE bids AS b SET b.status='R' WHERE id=%s", GetSQLValueString($_POST['bidID'], "int"));
			break;
		default:
			$updateSQL = sprintf("UPDATE bids AS b SET b.status='U' WHERE id=%s", GetSQLValueString($_POST['bidID'], "int"));
			break;
	}
	  
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
	
	  $updateGoTo = "proposals.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
	  }
	  header(sprintf("Location: %s", $updateGoTo));
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "bid")) {
	//validate input
	$err = "";
	if ( empty($_POST['pContractor']) ) $err .= "<li><strong>Contractor</strong> requires a value</li>";
	if ( empty($_POST['pSummary']) ) $err .= "<li><strong>Proposal Type</strong> requires a value</li>";
	if ( $_FILES["file"]["error"] == 4 ) $err .= "<li>A file has not been selected for upload</li>";

	if ( $err == "" ) {
		//upload file(s)
		if ( (($_FILES["file"]["type"]=="application/pdf") 
		|| ($_FILES["file"]["type"]=="application/msword") 
		|| ($_FILES["file"]["type"]=="application/vnd.openxmlformats-officedocument.wordprocessingml.document")
		|| ($_FILES["file"]["type"]=="application/msexcel") 
		|| ($_FILES["file"]["type"]=="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")) 		 
		&& ($_FILES["file"]["size"] < 15000000) ) {
			if ($_FILES["file"]["error"] > 0)
			{
				echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
				exit;
			}
			else
			{
				$file_name = $_POST['project'].time()."_".str_replace(" ","",$_FILES["file"]["name"]);
				move_uploaded_file($_FILES["file"]["tmp_name"],
				"../files/proposals/" . $file_name);
			}
		} else {
			echo "File cannot be uploaded due to invalid file type or size";
			exit;
		}
	
	  // save info to database
	  $vProject = $_POST['project'];
	  $vContractor = $_POST['pContractor'];
	  foreach ($_POST['pSummary'] as $vSummary) {
		  $insertSQL = sprintf("INSERT INTO bids (projectID, subID, appType, rfpFile, submitDate) VALUES (%s, %s, %s, %s, now())",
							   GetSQLValueString($vProject, "int"),
							   GetSQLValueString($vContractor, "int"),
							   GetSQLValueString($vSummary, "int"),
							   GetSQLValueString($file_name, "text"));
		
		  mysql_select_db($database_adminConn, $adminConn);
		  $Result1 = mysql_query($insertSQL, $adminConn) or die(mysql_error());
	  }
	
	  $insertGoTo = "proposals.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
		#$insertGoTo .= "&apply=submitted";
	  }
	  header(sprintf("Location: %s", $insertGoTo));
	}
}

$colname_rsProjectName = "-1";
if (isset($_GET['pid'])) {
  $colname_rsProjectName = $_GET['pid'];
}
mysql_select_db($database_adminConn, $adminConn);
$query_rsProjectName = sprintf("SELECT projName, projStatus FROM projects WHERE id = %s", GetSQLValueString($colname_rsProjectName, "int"));
$rsProjectName = mysql_query($query_rsProjectName, $adminConn) or die(mysql_error());
$row_rsProjectName = mysql_fetch_assoc($rsProjectName);
$totalRows_rsProjectName = mysql_num_rows($rsProjectName);

$colname_rsProposals = "-1";
if (isset($_GET['pid'])) {
  $colname_rsProposals = $_GET['pid'];
}
$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE (bids.status<>'R') and appType=vt_proposaltype.id and demographics.id=bids.subID and projectID = %s ORDER BY SubmitDate DESC, appSummary ASC", GetSQLValueString($colname_rsProposals, "int"));

if (isset($_POST['MM_view'])) {
	if ($_POST['MM_view'] == 'vstat') { // filter by Award Status
		switch ($_POST['submit']) {
			case 'Awarded':
				$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE appType=vt_proposaltype.id and demographics.id=bids.subID and projectID = %s and bids.status='A' ORDER BY SubmitDate DESC, appSummary ASC", GetSQLValueString($colname_rsProposals, "int"));
				break;
			case 'Tentative':
				$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE appType=vt_proposaltype.id and demographics.id=bids.subID and projectID = %s and bids.status='T' ORDER BY SubmitDate DESC, appSummary ASC", GetSQLValueString($colname_rsProposals, "int"));
				break;
			case 'Rejected':
				$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE appType=vt_proposaltype.id and demographics.id=bids.subID and projectID = %s and bids.status='R' ORDER BY SubmitDate DESC, appSummary ASC", GetSQLValueString($colname_rsProposals, "int"));
				break;
			case 'Unprocessed':
				$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE appType=vt_proposaltype.id and demographics.id=bids.subID and projectID = %s and bids.status='U' ORDER BY SubmitDate DESC, appSummary ASC", GetSQLValueString($colname_rsProposals, "int"));
				break;
			default:
				$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE appType=vt_proposaltype.id and demographics.id=bids.subID and projectID = %s ORDER BY SubmitDate DESC, appSummary ASC", GetSQLValueString($colname_rsProposals, "int"));
				break;
		}
	} else if ($_POST['MM_view'] == 'vtype') { // filter by Proposal Type
		if (isset($_POST['vtypeSelect']) && $_POST['vtypeSelect'] != 0) { 
				$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE appType=vt_proposaltype.id and demographics.id=bids.subID and appType = %s and projectID = %s ORDER BY SubmitDate DESC, appSummary ASC", 
				GetSQLValueString($_POST['vtypeSelect'], "int"),
				GetSQLValueString($colname_rsProposals, "int"));
		}
	} else if ($_POST['MM_view'] == 'vsub') { // filter by Contractor
		if (isset($_POST['vsubSelect']) && $_POST['vsubSelect'] != 0) { 
				$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE appType=vt_proposaltype.id and demographics.id=bids.subID and subID = %s and projectID = %s ORDER BY SubmitDate DESC, appSummary ASC", 
				GetSQLValueString($_POST['vsubSelect'], "int"),
				GetSQLValueString($colname_rsProposals, "int"));
		}
	}
}
mysql_select_db($database_adminConn, $adminConn);
/*$query_rsProposals = sprintf("SELECT demographics.id AS did, bids.id AS bid, FirstName, LastName, Company, status, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp FROM demographics, bids, vt_proposaltype WHERE appType=vt_proposaltype.id and demographics.id=bids.subID and projectID = %s ORDER BY SubmitDate DESC, appSummary ASC", GetSQLValueString($colname_rsProposals, "int"));*/
$rsProposals = mysql_query($query_rsProposals, $adminConn) or die(mysql_error());
$row_rsProposals = mysql_fetch_assoc($rsProposals);
$totalRows_rsProposals = mysql_num_rows($rsProposals);

mysql_select_db($database_adminConn, $adminConn);
$query_rsContractors = "SELECT id, FirstName, LastName FROM demographics WHERE demographics.delete=0 ORDER BY FirstName, LastName ASC";
$rsContractors = mysql_query($query_rsContractors, $adminConn) or die(mysql_error());
$row_rsContractors = mysql_fetch_assoc($rsContractors);
$totalRows_rsContractors = mysql_num_rows($rsContractors);

mysql_select_db($database_adminConn, $adminConn);
$query_rsProposalType = "SELECT * FROM vt_proposaltype ORDER BY proposalType ASC";
$rsProposalType = mysql_query($query_rsProposalType, $adminConn) or die(mysql_error());
$row_rsProposalType = mysql_fetch_assoc($rsProposalType);
$totalRows_rsProposalType = mysql_num_rows($rsProposalType);

mysql_select_db($database_adminConn, $adminConn);
$query_rsViewProposalType = sprintf("SELECT DISTINCT vt_proposaltype.id AS ptID, proposalType FROM vt_proposaltype, bids WHERE bids.appType=vt_proposaltype.id and bids.projectID=%s ORDER BY proposalType ASC", GetSQLValueString($_GET['pid'], "int"));
$rsViewProposalType = mysql_query($query_rsViewProposalType, $adminConn) or die(mysql_error());
$row_rsViewProposalType = mysql_fetch_assoc($rsViewProposalType);
$totalRows_rsViewProposalType = mysql_num_rows($rsViewProposalType);

mysql_select_db($database_adminConn, $adminConn);
$query_rsViewContractors = sprintf("SELECT DISTINCT d.id, FirstName, LastName FROM demographics AS d, bids WHERE d.delete=0 and bids.subID=d.id and bids.projectID=%s ORDER BY FirstName, LastName ASC", GetSQLValueString($_GET['pid'], "int"));
$rsViewContractors = mysql_query($query_rsViewContractors, $adminConn) or die(mysql_error());
$row_rsViewContractors = mysql_fetch_assoc($rsViewContractors);
$totalRows_rsViewContractors = mysql_num_rows($rsViewContractors);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="605" />
<title>RFP | Administration: Project Proposals</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../inc/popup.js"></script>
<style type="text/css">
/*@import url("../../inc/default.css");*/
@import url("../../inc/child.css");
@import url("../../rfp/rfp.css");
#proposals .propList table { width:55%; /*float:left;*/ }
#proposals .propList td.t1 { width:25%; }
#proposals .propList td.t2 { width:35%; }
#proposals .propList td.t3 { width:15%; }
#proposals .propList td.t4 { width:15%; text-align:center; }
#proposals .propList td.t5 { width:10%; }
#newprops { /*width:43%; float:right; margin-top:-8px;*/ 
 width:418px; position:absolute; margin-left:543px; top:505px; }
/*#newprops fieldset { margin:0; padding:0; }*/
#newprops legend { padding:0px 5px; }
#newprops table { margin:0; }
#newprops td.lbl { white-space:nowrap; text-align:right; }
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
<div id="proposals">
<h3><span><a href="project.php?func=edit&id=<?php echo $_GET['pid']; ?>"><span class="pname"><?php echo $row_rsProjectName['projName']; ?></span></a> :: Submitted Proposals</span></h3>
<?php require('admnav.php'); ?>
<?php if ($totalRows_rsProposals > 0) { // Show if recordset not empty ?>
<div class="propList">
<p>[ <a href="export.php?efunc=proposals&id=<?php echo $colname_rsProposals; ?>">Export Contractor Info</a> (includes Award Status) ]</p>
<form id="vstat" name="vstat" action="<?php echo $editFormAction; ?>" method="post">
<p>Filter by Award Status: 
<input type="submit" name="submit" id="submit" value="Awarded" <?php echo('class="'.($_POST['MM_view']=='vstat' && $_POST['submit']=='Awarded' ? 'btn here' : 'btn').'"'); ?> />
<input type="submit" name="submit" id="submit" value="Tentative" <?php echo('class="'.($_POST['MM_view']=='vstat' && $_POST['submit']=='Tentative' ? 'btn here' : 'btn').'"'); ?> />
<input type="submit" name="submit" id="submit" value="Rejected" <?php echo('class="'.($_POST['MM_view']=='vstat' && $_POST['submit']=='Rejected' ? 'btn here' : 'btn').'"'); ?> />
<input type="submit" name="submit" id="submit" value="Unprocessed" <?php echo('class="'.($_POST['MM_view']=='vstat' && $_POST['submit']=='Unprocessed' ? 'btn here' : 'btn').'"'); ?> />
<input type="submit" name="submit" id="submit" value="All" <?php echo('class="'.($_POST['MM_view']=='vstat' && $_POST['submit']=='All' ? 'btn here' : 'btn').'"'); ?> />
<input type="hidden" id="MM_view" name="MM_view" value="vstat" />
<input type="hidden" id="proj" name="proj" value="<?php echo($_GET['pid']); ?>" />
</p>
</form>
<form id="vtype" name="vtype" action="<?php echo $editFormAction; ?>" method="post">
<p>Filter by Proposal Type:
<select name="vtypeSelect" id="vtypeSelect" onchange="this.form.submit()">
  <option value="0">- all -</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsViewProposalType['ptID']?>"<?php if ($row_rsViewProposalType['ptID']==$_POST['vtypeSelect']) { echo(' selected="selected"'); } ?>><?php echo $row_rsViewProposalType['proposalType']?></option>
  <?php
} while ($row_rsViewProposalType = mysql_fetch_assoc($rsViewProposalType));
  $rows = mysql_num_rows($rsViewProposalType);
  if($rows > 0) {
      mysql_data_seek($rsViewProposalType, 0);
	  $row_rsViewProposalType = mysql_fetch_assoc($rsViewProposalType);
  }
?>
</select>
<input type="hidden" id="MM_view" name="MM_view" value="vtype" />
<input type="hidden" id="proj" name="proj" value="<?php echo($_GET['pid']); ?>" />
</p>
</form>
<form id="vsub" name="vsub" action="<?php echo $editFormAction; ?>" method="post">
<p>Filter by Contractor:
<select name="vsubSelect" id="vsubSelect" onchange="this.form.submit()">
  <option value="0">- all -</option>
  <?php
do {  
?>
  <option value="<?php echo $row_rsViewContractors['id']?>"<?php if ($row_rsViewContractors['id']==$_POST['vsubSelect']) { echo(' selected="selected"'); } ?>><?php echo $row_rsViewContractors['FirstName']." ".$row_rsViewContractors['LastName'] ?></option>
  <?php
} while ($row_rsViewContractors = mysql_fetch_assoc($rsViewContractors));
  $rows = mysql_num_rows($rsViewContractors);
  if($rows > 0) {
      mysql_data_seek($rsViewContractors, 0);
	  $row_rsViewContractors = mysql_fetch_assoc($rsViewContractors);
  }
?>
</select>
<input type="hidden" id="MM_view" name="MM_view" value="vsub" />
<input type="hidden" id="proj" name="proj" value="<?php echo($_GET['pid']); ?>" />
</p>
</form>
  <table border="0" cellspacing="1" cellpadding="4" summary="list of submitted proposals">
    <tr>
      <th scope="col">Proposal Type</th>
      <th scope="col">Contractor Info</th>
      <th scope="col">Submitted</th>
      <th scope="col" style="text-align:center">Award Status</th>
    </tr>
    <?php do { ?>
      <tr>
        <td class="t1"><a href="/rfp/files/proposals/<?php echo($row_rsProposals['rfpFile']); ?>" target="_blank"><?php echo $row_rsProposals['appSummary']; ?></a></td>
        <td class="t2"><?php echo $row_rsProposals['FirstName']; ?> <?php echo $row_rsProposals['LastName']; ?>
		<?php if ( !empty($row_rsProposals['Company']) ) echo '<br /><em>'.$row_rsProposals['Company'].'</em>'; ?></td>
        <td class="t3"><?php echo date('m-d-Y g:ia',$row_rsProposals['datetimestamp']); ?></td>
        <td class="t4">
        <?php 
		if ( !(strcmp($row_rsProjectName['projStatus'],"C")) || $_SESSION['rfp_adm_MM_UserGroup']==4 || $_REQUEST['func']=='view' ) { 
			switch ($row_rsProposals['status']) {
				case 'A':
					echo '<strong>Awarded</strong>';
					break;
				case 'T':
					echo '<strong>Tentative</strong>';
					break;
				case 'R':
					echo '<strong>Rejected</strong>';
					break;
				default:
					echo '';
					break;
			}
        } else { // status not complete 
		?>
       <form id="status" name="status" method="post" action="<?php echo $editFormAction; ?>">
		<input type="hidden" id="MM_insert" name="MM_insert" value="bstat" />
        <input type="hidden" id="bidID" name="bidID" value="<?php echo $row_rsProposals['bid']; ?>" />
		<?php 
			switch ($row_rsProposals['status']) {
				case 'A':
					echo '<strong>Awarded</strong><br /><input type="submit" name="submit" id="submit" value="Reject" class="btn" />';
					break;
				case 'T':
					echo '<strong>Tentative</strong><br /><input type="submit" name="submit" id="submit" value="Award" class="btn" /> <input type="submit" name="submit" id="submit" value="Reject" class="btn" />';
					break;
				case 'R':
					echo '<strong>Rejected</strong><br /><input type="submit" name="submit" id="submit" value="Award" class="btn" /> <input type="submit" name="submit" id="submit" value="Tent." class="btn" />';
					break;
				default:
					echo '<input type="submit" name="submit" id="submit" value="Award" class="btn" /> <input type="submit" name="submit" id="submit" value="Tent." class="btn" /> <input type="submit" name="submit" id="submit" value="Reject" class="btn" />';
					break;
			}
		?>
        </form>
        <?php } //end Project Complete status ?>
        </td>
      </tr>
      <?php } while ($row_rsProposals = mysql_fetch_assoc($rsProposals)); ?>
  </table>
</div>  
  <?php }  else { // show if recordset is empty ?>
  	<?php if (isset($_POST['MM_view']) && $_POST['MM_view']=='vstat') { // show if filter is empty ?>
      <p>There are currently no proposals submitted with an Award Status &quot;<strong><?php echo($_POST['submit']); ?></strong>&quot;</p>
      <form id="vstat" name="vstat" action="<?php echo $editFormAction; ?>" method="post">
        <input type="submit" name="submit" id="submit" value="View All" class="btn" />
        <input type="hidden" id="MM_view" name="MM_view" value="vstat" />
        <input type="hidden" id="proj" name="proj" value="<?php echo($_GET['pid']); ?>" />
      </form>
	<?php } else { // show if non-filtered recordset is empty ?>
	  <p>There are currently no proposals submitted for this project.</p>
    <?php } //end recordest filter ?>
  <?php } // end recordset visibility ?>
<?php if ( !(strcmp($row_rsProjectName['projStatus'],"C")) || $_SESSION['rfp_adm_MM_UserGroup']<>4 && $_REQUEST['func']<>'view' ) { ?>
<div id="newprops">
<form name="newbid" action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" id="newbid">
<fieldset>
<legend>Add New Proposal</legend>
<p><span class="req">*</span> <span class="note">Denotes a required field</span></p>
<?php
if ( $err != "" ) {
	echo '<div class="err"><p>Proposal not submitted due to the following:</p><ul>';
	echo $err;
	echo '</ul></div>';
}
?>
<table border="0" cellspacing="1" cellpadding="4" summary="request for proposal form">
  <tr>
  <td class="lbl t1"><span class="req">*</span> <label for="pContractor">Contractor</label></td>
  <td class="t2"><select name="pContractor" id="pContractor">
    <option value=""></option>
    <?php
do {  
?>
    <option value="<?php echo $row_rsContractors['id']?>"><?php echo $row_rsContractors['FirstName']?> <?php echo $row_rsContractors['LastName']?></option>
    <?php
} while ($row_rsContractors = mysql_fetch_assoc($rsContractors));
  $rows = mysql_num_rows($rsContractors);
  if($rows > 0) {
      mysql_data_seek($rsContractors, 0);
	  $row_rsContractors = mysql_fetch_assoc($rsContractors);
  }
?>
  </select></td>
  </tr>
  <tr>
    <td class="lbl t1"><span class="req">*</span> <label for="pSummary">Proposal Type</label></td>
    <td class="t2"><select multiple="multiple" size="1" name="pSummary[]" id="pSummary">
      <option value=""></option>
      <?php
do {  
?>
      <option value="<?php echo $row_rsProposalType['id']?>"<?php if ( isset($_POST['pSummary']) && $_POST['pSummary']==$row_rsProposalType['id']) echo ' selected="selected"'; ?>><?php echo $row_rsProposalType['proposalType']?></option>
      <?php
} while ($row_rsProposalType = mysql_fetch_assoc($rsProposalType));
  $rows = mysql_num_rows($rsProposalType);
  if($rows > 0) {
      mysql_data_seek($rsProposalType, 0);
	  $row_rsProposalType = mysql_fetch_assoc($rsProposalType);
  }
?>
      </select> <span class="note">Use 'Ctrl' to select multiple values</span></td>
  </tr>
  <tr>
    <td class="lbl t1"><span class="req">*</span> <label for="file">Upload</label></td>
    <td class="t2"><input type="hidden" name="MAX_FILE_SIZE" value="15000000" />
<input name="file" type="file" class="file" id="file" size="36" /><br />
<span class="note">File must be a PDF (.pdf), Word (.doc or .docx), or Excel (.xls or .xlsx) document no larger than 15MB</span> </td>
    </tr>
</table>
    <p class="btns">
      <input type="submit" name="submit" id="submit" value="Submit" class="btn" />
      <input type="hidden" name="project" id="project" value="<?php echo $_GET['pid']; ?>" />
    </p>
<input type="hidden" name="MM_insert" value="bid" />
</fieldset>
</form>
</div>
<?php } // end check Project Complete ?>
</div>
</div>

<?php require('../../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsProjectName);
mysql_free_result($rsProposals);
mysql_free_result($rsContractors);
mysql_free_result($rsProposalType);
mysql_free_result($rsViewProposalType);
mysql_free_result($rsViewContractors);
?>
