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
/*if (!isset($_SESSION)) {
  session_start();
}*/
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
if (!((isset($_SESSION['rfp_MM_Username'])) && (isAuthorized("",$MM_authorizedUsers, $_SESSION['rfp_MM_Username'], $_SESSION['rfp_MM_UserGroup'])))) {   
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

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "bid")) {
	//validate input
	$err = "";
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
				"files/proposals/" . $file_name);
			}
		} else {
			echo "File cannot be uploaded due to invalid file type or size";
			exit;
		}
	
	  // save info to database
	  $vProject = $_POST['project'];
	  $vSession = $_SESSION['profileID'];
	  foreach ($_POST['pSummary'] as $vSummary) {
		  $insertSQL = sprintf("INSERT INTO bids (projectID, subID, appType, rfpFile, submitDate) VALUES (%s, %s, %s, %s, now())",
							   GetSQLValueString($vProject, "int"),
							   GetSQLValueString($vSession, "int"),
							   GetSQLValueString($vSummary, "int"),
							   GetSQLValueString($file_name, "text"));
		
		  mysql_select_db($database_adminConn, $adminConn);
		  $Result1 = mysql_query($insertSQL, $adminConn) or die(mysql_error());
	  }
	
	  $insertGoTo = "contract.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
		#$insertGoTo .= "&apply=submitted";
	  }
	  header(sprintf("Location: %s", $insertGoTo));
	}
}

if ( (isset($_POST['dzip']) && $_POST['dzip']=='Download Selected') ) {
	// Prepare File 
	$file = tempnam("tmp", "zip"); 
	$zip = new ZipArchive(); 
	$zip->open($file, ZipArchive::OVERWRITE); 
	$path = 'files/plans/';
	
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

$projID = '';
if ( isset($_SESSION['project_id']) && $_SESSION['project_id'] != '' ) {
	$projID = $_SESSION['project_id'];
}
if ( isset($_REQUEST['id']) && $_REQUEST['id'] ) {
	$projID = $_REQUEST['id'];
}

if ( $projID !='' ) {
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsProject = sprintf("SELECT projName, projAddress, projState, projCity, projZip, projDetail, rfpInfo FROM projects WHERE id = %s", GetSQLValueString($projID, "int"));
	$rsProject = mysql_query($query_rsProject, $adminConn) or die(mysql_error());
	$row_rsProject = mysql_fetch_assoc($rsProject);
	$totalRows_rsProject = mysql_num_rows($rsProject);
	
	mysql_select_db($database_adminConn, $adminConn);
	if ( (isset($_POST["MM_view"]) && $_POST["MM_view"]=='psview') ) {
		if ( $_POST["submit"]=='View all plans' ) {
			$query_rsPlans = sprintf("SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and projectID = %s ORDER BY sheet ASC", GetSQLValueString($projID, "int"));
			$psView = 'all';
		} else {
			$query_rsPlans = sprintf("SELECT * FROM (SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and projectID = %s ORDER BY sheet ASC) AS tbl GROUP BY sheet", GetSQLValueString($projID, "int"));
			$psView ='';
		}
		if ( isset($_POST["vptSelect"]) && $_POST["vptSelect"]<>0 ) {
			$vpt = $_POST["vptSelect"];
			if ($_POST["hview"]=='all') {
				$query_rsPlans = sprintf("SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and vt_plantype.id = %s and projectID = %s ORDER BY sheet ASC", GetSQLValueString($vpt, "int"), GetSQLValueString($projID, "int"));
				$psView='all';
			} else {
				$query_rsPlans = sprintf("SELECT * FROM (SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and vt_plantype.id = %s and projectID = %s ORDER BY sheet ASC) AS tbl GROUP BY sheet", GetSQLValueString($vpt, "int"), GetSQLValueString($projID, "int"));
				$psView='';
			}
		}
	} else {
		$query_rsPlans = sprintf("SELECT * FROM (SELECT planspecs.id, sheet, title, vt_plantype.planType AS planType, revisionDate, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and projectID = %s ORDER BY sheet ASC) AS tbl GROUP BY sheet", GetSQLValueString($projID, "int"));
	}
	$rsPlans = mysql_query($query_rsPlans, $adminConn) or die(mysql_error());
	$row_rsPlans = mysql_fetch_assoc($rsPlans);
	$totalRows_rsPlans = mysql_num_rows($rsPlans);

	mysql_select_db($database_adminConn, $adminConn);
	$query_rsBids = sprintf("SELECT vt_proposaltype.proposalType AS proposalType, submitDate, rfpFile FROM bids, vt_proposaltype WHERE bids.appType = vt_proposaltype.id and projectID = %s and subID = %s ORDER BY proposalType, submitDate ASC", 
		GetSQLValueString($projID, "int"),
		GetSQLValueString($_SESSION['profileID'], "int"));
	$rsBids = mysql_query($query_rsBids, $adminConn) or die(mysql_error());
	$row_rsBids = mysql_fetch_assoc($rsBids);
	$totalRows_rsBids = mysql_num_rows($rsBids);
} else {
	mysql_select_db($database_adminConn, $adminConn);
	$query_rsProjects = "SELECT id, projName, projSummary, projCity, projState FROM projects WHERE projStatus='O' ORDER BY projName ASC";
	$rsProjects = mysql_query($query_rsProjects, $adminConn) or die(mysql_error());
	$row_rsProjects = mysql_fetch_assoc($rsProjects);
	$totalRows_rsProjects = mysql_num_rows($rsProjects);

	mysql_select_db($database_adminConn, $adminConn);
	$query_rsContracts = "SELECT projects.id, projName, projSummary, projCity, projState FROM projects JOIN bids ON projects.id=bids.projectID WHERE ((bids.status='A' or bids.status='T') AND projStatus='W') ORDER BY projName ASC";
	$rsContracts = mysql_query($query_rsContracts, $adminConn) or die(mysql_error());
	$row_rsContracts = mysql_fetch_assoc($rsContracts);
	$totalRows_rsContracts = mysql_num_rows($rsContracts);
}

mysql_select_db($database_adminConn, $adminConn);
$query_rsProposalType = "SELECT * FROM vt_proposaltype ORDER BY proposalType ASC";
$rsProposalType = mysql_query($query_rsProposalType, $adminConn) or die(mysql_error());
$row_rsProposalType = mysql_fetch_assoc($rsProposalType);
$totalRows_rsProposalType = mysql_num_rows($rsProposalType);

mysql_select_db($database_adminConn, $adminConn);
$query_rsPlanType = sprintf("SELECT DISTINCT planTypeID, planType FROM planspecs, vt_plantype WHERE planTypeID=vt_plantype.id AND planspecs.projectID = %s ORDER BY planType ASC", GetSQLValueString($projID, "int"));
$rsPlanType = mysql_query($query_rsPlanType, $adminConn) or die(mysql_error());
$row_rsPlanType = mysql_fetch_assoc($rsPlanType);
$totalRows_rsPlanType = mysql_num_rows($rsPlanType);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="605" />
<title>RFP | Contractor Access: Project Detail</title>
<script type="text/javascript" src="/inc/checkAll.js"></script>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="/inc/popup.js"></script>
<style type="text/css">
/*@import url("/inc/default.css");*/
@import url("/inc/child.css");
@import url("/rfp/rfp.css");
.logout { text-align: right; }
.locale td.t1, .locale td.t3 { width:20%; }
.locale { padding-bottom:35px; }
form#planspecs table { margin-left:0; }
form#bid { float:left; width:60%; margin-right:10px; padding:5px; }
#bids { margin: 1.5em auto; }
table#files { background:#999; width:360px; }
table#files caption { text-align:left; font-weight:bold; font-size:11pt; }
table#files th { background-color:#ccc; }
table#files td {background-color:#fff; }
table#files td.t1 { width:70%; }
table#files td.t2 { width:30%; }
#specs { clear:both; padding-top:10px; }
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
<p class="logout"><?php if ($projID=='') { ?><a href="profile.php" class="btn">Update Profile</a><?php } ?> <?php if ($projID!='') { unset($_SESSION['project_id']); ?><a href="contract.php" class="btn">Project List</a><?php } ?> <a href="<?php echo $logoutAction ?>" class="btn">Logout</a></p>
<?php if ( $projID != '' ) { ?>
<div id="rfp">
<h3><span><?php echo $row_rsProject['projName']; ?></span></h3>
<div id="pinfo"><p>[ <?php echo (!empty($row_rsProject['projAddress']) ? $row_rsProject['projAddress'].', ' : ''); ?><?php echo $row_rsProject['projCity']; ?>, <?php echo $row_rsProject['projState']; ?><?php echo (!empty($row_rsProject['projZip']) ? ', '.$row_rsProject['projZip'] : ''); ?> ]</p></div>
<div id="psumm">
<p><?php echo nl2br($row_rsProject['projDetail']); ?></p>
</div>
<div id="rfpinfo">
<h4 style="margin-bottom:0"><span>RFP Specifications</span></h4>
<?php echo nl2br($row_rsProject['rfpInfo']); ?>
</div>
<div id="bids">
<form name="bid" action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" id="bid">
<fieldset>
<legend>Submit a Proposal</legend>
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
    <td class="lbl"><span class="req">*</span> <label for="pSummary">Proposal Type</label></td>
    <td><select multiple="multiple" size="1" name="pSummary[]" id="pSummary">
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
    <td class="lbl"><span class="req">*</span> <label for="file">Upload</label></td>
    <td><input type="hidden" name="MAX_FILE_SIZE" value="15000000" />
<input name="file" type="file" class="file" id="file" size="61" /><br />
<span class="note">File must be a PDF (.pdf), Word (.doc or .docx), or Excel (.xls or .xlsx) document no larger than 15MB</span> </td>
    </tr>
</table>
    <p class="btns">
      <input type="submit" name="submit" id="submit" value="Submit" class="btn" />
      <a href="contract.php?id=<?php echo $_GET['id']; ?>" class="btn"><span>Cancel</span></a>
      <input type="hidden" name="project" id="project" value="<?php echo $_GET['id']; ?>" />
    </p>
<input type="hidden" name="MM_insert" value="bid" />
</fieldset>
</form>
<?php if ($totalRows_rsBids > 0) { // Show if recordset not empty ?>
  <table name="files" id="files" border="0" cellspacing="1" cellpadding="4" summary="list of bids already submitted for this project">
    <caption>Submitted Proposals</caption>
    <tr>
      <th class="lbl">Proposal Type</th>
      <th class="lbl">Date Submitted</th>
      </tr>
    <?php do { ?>
    <tr>
      <td class="t1"><a href="file.php?id=bids&fn=<?php echo $row_rsBids['rfpFile']; ?>"><?php echo $row_rsBids['proposalType']; ?></a></td>
      <td class="t2" style="white-space:nowrap"><?php echo date('Y-m-d h:i A', strtotime($row_rsBids['submitDate'])); ?></td>
      </tr>
	<?php } while ($row_rsBids = mysql_fetch_assoc($rsBids)); ?>
      
  </table>
  <?php } // Show if recordset not empty ?>
</div>
<?php if ($totalRows_rsPlans > 0) { // Show if recordset not empty ?>
<div id="specs">
<h3><span>Plans &amp; Specifications</span></h3>
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
          </tr>
      </thead>
      <tbody>
        <?php
  	$idx = 0;
	do { ?>
          <tr>
            <td class="t0"><input type="checkbox" id="zip_<?php echo $idx; ?>" name="zip_<?php echo $idx; ?>" value="<?php echo $row_rsPlans['filename']; ?>" onclick="CheckCheckAll(document.specslist);" class="chkbx" /></td>
            <td class="t1"><?php echo $row_rsPlans['sheet']; ?></td>
            <td class="t2"><a href="file.php?id=plans&fn=<?php echo $row_rsPlans['filename']; ?>"><?php echo $row_rsPlans['title']; ?></a></td>
            <td class="t3"><?php echo $row_rsPlans['planType']; ?></td>
            <td class="t4"><?php echo $row_rsPlans['revisionDate']; ?></td>
          </tr>
          <?php 
	$idx = $idx + 1;
	} while ($row_rsPlans = mysql_fetch_assoc($rsPlans)); ?>
      </tbody>
    </table>
    <div style="padding-top:10px;"><input id="dzip" name="dzip" type="submit" value="Download Selected" class="btn" /></div>
  </form>
</div>
<?php } //end if recordset not empty ?>
</div>
<?php } else { //show project list ?>
<div id="projlist">
<?php if ($totalRows_rsProjects == 0) { // Show if recordset empty ?>
  <p>There are currently no projects in development that are accepting a <em>Request for Proposal</em> (<em>RFP</em>). Please check back often as this may change.</p>
<?php } // Show if recordset empty ?>
<?php if ($totalRows_rsProjects > 0) { // Show if recordset not empty ?>
  <div id="rfplist">
    <div class="locale">
      <table border="0" cellspacing="1" cellpadding="4" summary="list of current projects">
        <caption>Open Projects</caption>
        <thead>
          <tr>
            <th scope="col">Project Name</th>
            <th scope="col">Summary</th>
            <th scope="col">Location</th>
            </tr>
        </thead>
        <tbody>
          <?php do { ?>
            <tr>
              <td class="t1"><a href="contract.php?id=<?php echo $row_rsProjects['id']; ?>"><?php echo $row_rsProjects['projName']; ?></a></td>
              <td class="t2"><?php echo $row_rsProjects['projSummary']; ?></td>
              <td class="t3"><?php echo $row_rsProjects['projCity']; ?>, <?php echo $row_rsProjects['projState']; ?></td>
            </tr>
            <?php } while ($row_rsProjects = mysql_fetch_assoc($rsProjects)); ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php } // Show if recordset not empty ?>
  <p></p>
<?php if ($totalRows_rsContracts > 0) { // Show if recordset not empty ?>
  <div id="rfplist">
    <div class="locale">
      <table border="0" cellspacing="1" cellpadding="4" summary="list of awarded or tentatively awarded projects">
        <caption>Under Construction</caption>
        <thead>
          <tr>
            <th scope="col">Project Name</th>
            <th scope="col">Summary</th>
            <th scope="col">Location</th>
            </tr>
        </thead>
        <tbody>
          <?php do { ?>
            <tr>
              <td class="t1"><a href="contract.php?id=<?php echo $row_rsContracts['id']; ?>"><?php echo $row_rsContracts['projName']; ?></a></td>
              <td class="t2"><?php echo $row_rsContracts['projSummary']; ?></td>
              <td class="t3"><?php echo $row_rsContracts['projCity']; ?>, <?php echo $row_rsContracts['projState']; ?></td>
            </tr>
            <?php } while ($row_rsContracts = mysql_fetch_assoc($rsContracts)); ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php } // Show if recordset not empty ?>
  </div>
<?php } //endif - project detail or project list ?>
</div>
<?php require('../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsProject);
mysql_free_result($rsPlans);
mysql_free_result($rsProjects);
mysql_free_result($rsContracts);
mysql_free_result($rsProposalType);
?>
