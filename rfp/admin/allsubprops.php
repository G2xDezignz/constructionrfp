<?php require_once('../../Connections/adminConn.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
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

$sid = "-1";
if (isset($_GET['sid'])) {
  $sid = $_GET['sid'];
}

mysql_select_db($database_adminConn, $adminConn);

$query_rsSub = sprintf("SELECT FirstName, LastName, Title, Trade, Company, Address, City, State, Zip, Email, Phone, Mobile, Fax FROM demographics WHERE id = %s", GetSQLValueString($sid, "int"));
$rsSub = mysql_query($query_rsSub, $adminConn) or die(mysql_error());
$row_rsSub = mysql_fetch_assoc($rsSub);
$totalRows_rsSub = mysql_num_rows($rsSub);

$query_rsProposals = sprintf("SELECT DISTINCT projects.id AS pid, projName, rfpFile, proposalType AS appSummary, UNIX_TIMESTAMP(SubmitDate) AS datetimestamp, status FROM demographics, projects, bids, vt_proposaltype WHERE bids.appType=vt_proposaltype.id and bids.projectID=projects.id and subID = %s ORDER BY SubmitDate DESC, projName ASC, appSummary ASC", GetSQLValueString($sid, "int"));
$rsProposals = mysql_query($query_rsProposals, $adminConn) or die(mysql_error());
$row_rsProposals = mysql_fetch_assoc($rsProposals);
$totalRows_rsProposals = mysql_num_rows($rsProposals);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RFP | Administration: All Project Proposals by Contractor</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../inc/popup.js"></script>
<style type="text/css">
/*@import url("../../inc/default.css");*/
@import url("../../inc/child.css");
@import url("../../rfp/rfp.css");
#proposals .propList table { width:80%; }
#proposals .propList td.t1 { width:25%; }
#proposals .propList td.t2 { width:35%; }
#proposals .propList td.t3 { width:15%; }
#proposals .propList td.t4 { width:15%; text-align:center; }
#proposals .propList td.t5 { width:10%; }
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
<h3><span>Subcontractor :: All Submitted Proposals</span></h3>
<?php require('admnav.php'); ?>
<?php
	echo '<p>'.$row_rsSub['FirstName'].' '.$row_rsSub['LastName'];
	echo ($row_rsSub['Title']<>'' ? '<br />'.$row_rsSub['Title'] : '');
	echo ($row_rsSub['Company']<>'' ? '<br />'.$row_rsSub['Company'] : '');
	echo '<br />'.$row_rsSub['Address'].', '.$row_rsSub['City'].' '.$row_rsSub['Zip'];
	echo ($row_rsSub['Trade']<>'' ? '<br />Trade: '.$row_rsSub['Trade'] : '');
	echo '<br />[p] '.$row_rsSub['Phone'];
	echo ($row_rsSub['Mobile']<>'' ? '<br />[m] '.$row_rsSub['Mobile'] : '');
	echo ($row_rsSub['Fax']<>'' ? '<br />[f] '.$row_rsSub['Fax'] : '');
	echo '<br />'.$row_rsSub['Email'].'</p>';
?>
<?php if ($totalRows_rsProposals > 0) { // Show if recordset not empty ?>
<div class="propList">
  <table border="0" cellspacing="1" cellpadding="4" summary="list of submitted proposals">
    <tr>
      <th scope="col">Proposal Type</th>
      <th scope="col">Project</th>
      <th scope="col">Submitted</th>
      <th scope="col" style="text-align:center">Award Status</th>
    </tr>
    <?php do { ?>
      <tr>
        <td class="t1"><a href="/rfp/files/proposals/<?php echo($row_rsProposals['rfpFile']); ?>" target="_blank"><?php echo $row_rsProposals['appSummary']; ?></a></td>
        <td class="t2"><a href="project.php?func=view&id=<?php echo $row_rsProposals['pid']; ?>"><?php echo $row_rsProposals['projName'];?></a></td>
        <td class="t3"><?php echo date('m-d-Y g:ia',$row_rsProposals['datetimestamp']); ?></td>
        <td class="t4">
        <?php 
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
		?>
        </td>
      </tr>
      <?php } while ($row_rsProposals = mysql_fetch_assoc($rsProposals)); ?>
  </table>
</div>  
  <?php }  else { // show if recordset is empty ?>
  <p>There are currently no proposals submitted for this subcontractor.</p>
  <?php } // end recordset visibility ?>
</div>
</div>

<?php require('../../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsSub);
mysql_free_result($rsProposals);
?>
