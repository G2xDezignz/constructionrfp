<?php require_once('../Connections/webConn.php'); ?>
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

$colname_rsProject = "-1";
if (isset($_GET['id'])) {
  $colname_rsProject = $_GET['id'];
}
mysql_select_db($database_webConn, $webConn);
$query_rsProject = sprintf("SELECT id, projName, projAddress, projState, projCity, projZip, projSummary, projDetail, rfpInfo, closeDate FROM projects WHERE id = %s", GetSQLValueString($colname_rsProject, "int"));
$rsProject = mysql_query($query_rsProject, $webConn) or die(mysql_error());
$row_rsProject = mysql_fetch_assoc($rsProject);
$totalRows_rsProject = mysql_num_rows($rsProject);
if ( $totalRows_rsProject < 1 ) { // Project cannot be found in database, redirect
	header( 'Location: /' ) ;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RFP | Request for Proposal: <?php echo $row_rsProject['projName']; ?></title>
<script type="text/javascript" src="../inc/checkAll.js"></script>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="/inc/popup.js"></script>
<style type="text/css">
/*@import url("/inc/default.css");*/
@import url("/inc/child.css");
@import url("/rfp/rfp.css");
</style>
</head>

<body>
<div id="layout">
<?php require('../inc/header.php'); ?>
<div id="content">
<div id="topslide" class="rfp">
<p><span></span></p>
</div>
<div id="content_layout">
<div id="left">
<h2 style="margin-bottom:-1em;font-size:13px;"><span>RFP Application</span></h2><p>If you are interested in submitting a proposal for this project, and have not done so already, please <a href="/rfp/login.php?id=<?php echo $row_rsProject['id'];?>">sign-in</a> or <a href="/rfp/apply.php?id=<?php echo $row_rsProject['id'];?>">register</a>.</p>
</div>
<div id="maincontent-rspan">
<h2><span>Project: <?php echo $row_rsProject['projName']; ?></span></h2>
<div id="crumbs"><p>&laquo; <a href="../planroom.php?pid=current">return to Project list</a> &raquo;</p></div>
<div id="rfp">
<div id="pinfo"><p>[ <?php echo (!empty($row_rsProject['projAddress']) ? $row_rsProject['projAddress'].', ' : ''); ?><?php echo $row_rsProject['projCity']; ?>, <?php echo $row_rsProject['projState']; ?><?php echo (!empty($row_rsProject['projZip']) ? ', '.$row_rsProject['projZip'] : ''); ?> ]</p></div>
<div id="psumm">
<?php echo nl2br($row_rsProject['projDetail']); ?>
</div>
<div id="rfpinfo">
<h4 style="margin-bottom:0"><span>Proposal Specifications</span></h4>
<?php echo nl2br($row_rsProject['rfpInfo']); ?>
</div>
<div><p>[ <a href="/rfp/login.php?id=<?php echo $row_rsProject['id'];?>">Sign-in</a> or <a href="/rfp/apply.php?id=<?php echo $row_rsProject['id'];?>">register</a> to see plan details and specifications. ]</p></div>
</div>
</div>
</div>
</div>
<?php require('../inc/footer.php'); ?>
</div>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsProject);
mysql_free_result($rsPlans);
?>
