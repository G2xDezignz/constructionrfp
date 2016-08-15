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


if ((isset($_POST['MM_delete'])) && ($_POST['MM_delete'] == "dplans")) {
  //delete file
  unlink("../files/plans/".$_POST['filename']);

  //delete from database
  $deleteSQL = sprintf("DELETE FROM planspecs WHERE id=%s",
                       GetSQLValueString($_POST['plans'], "int"));

  mysql_select_db($database_adminConn, $adminConn);
  $Result1 = mysql_query($deleteSQL, $adminConn) or die(mysql_error());

  $deleteGoTo = "project.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $deleteGoTo .= (strpos($deleteGoTo, '?')) ? "&" : "?";
    $deleteGoTo .= $_SERVER['QUERY_STRING'];
	$deleteGoTo .= "&func=edit&id=" . $_REQUEST['project'];
  } else {
	$deleteGoTo .= "?func=edit&id=" . $_REQUEST['project'];
  }
  header(sprintf("Location: %s", $deleteGoTo));
}

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "eplans")) {
	//validate input
	$err = "";
	if ( empty($_POST['sheet']) ) $err .= "<li><strong>Sheet</strong> requires a value</li>";
	if ( empty($_POST['title']) ) $err .= "<li><strong>Title</strong> requires a value</li>";
	if ( empty($_POST['ptype']) ) $err .= "<li><strong>Plan Type</strong> requires a value</li>";
	if ( empty($_POST['pdate']) ) $err .= "<li><strong>Revision Date</strong> requires a value</li>";

	if ($err == "") {
		//upload file
		if ($_FILES["file"]["error"] > 0)
		{
			//echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
			//exit;
			/* no file to upload, keep current file */
			$file_name = $_POST['filename'];
		}
		else
		{
			unlink("../files/plans/".$_POST['filename']);
			$file_name = $_POST['project']."_".str_replace(" ","",$_FILES["file"]["name"]);
			move_uploaded_file($_FILES["file"]["tmp_name"],
			"../files/plans/" . $file_name);
		}
	
		// save info to database
	  $updateSQL = sprintf("UPDATE planspecs SET projectID=%s, sheet=%s, title=%s, planTypeID=%s, revisionDate=%s, filename=%s WHERE id=%s",
						   GetSQLValueString($_POST['project'], "int"),
						   GetSQLValueString($_POST['sheet'], "text"),
						   GetSQLValueString($_POST['title'], "text"),
						   GetSQLValueString($_POST['ptype'], "int"),
						   GetSQLValueString($_POST['pdate'], "date"),
						   GetSQLValueString($file_name, "text"),
						   GetSQLValueString($_POST['id'], "int"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($updateSQL, $adminConn) or die(mysql_error());
	
	  $updateGoTo = "project.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
		$updateGoTo .= $_SERVER['QUERY_STRING'];
		$updateGoTo .= "&func=edit&id=" . $_REQUEST['project'];
	  } else {
		$updateGoTo .= "?func=edit&id=" . $_REQUEST['project'];
	  }
	  header(sprintf("Location: %s", $updateGoTo));
	}
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "nplans")) {
	//validate input
	$err = "";
	if ( empty($_POST['sheet']) ) $err .= "<li><strong>Sheet</strong> requires a value</li>";
	if ( empty($_POST['title']) ) $err .= "<li><strong>Title</strong> requires a value</li>";
	if ( empty($_POST['ptype']) ) $err .= "<li><strong>Plan Type</strong> requires a value</li>";
	if ( empty($_POST['pdate']) ) $err .= "<li><strong>Revision Date</strong> requires a value</li>";
	if ( $_FILES["file"]["error"] == 4 ) $err .= "<li>A file has not been selected for upload</li>";

	if ($err == "") {
		//upload file
		if ( (($_FILES["file"]["type"]=="application/pdf") 
			|| ($_FILES["file"]["type"]=="application/msword") 
			|| ($_FILES["file"]["type"]=="application/vnd.openxmlformats-officedocument.wordprocessingml.document") 
			|| ($_FILES["file"]["type"]=="application/msexcel") 
			|| ($_FILES["file"]["type"]=="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")) 
			&& ($_FILES["file"]["size"] < 25000000) ) {
			if ($_FILES["file"]["error"] > 0)
			{
				echo "File Error Code: " . $_FILES["file"]["error"] . "<br />";
				exit;
			}
			else
			{
				$file_name = $_POST['project']."_".str_replace(" ","",$_FILES["file"]["name"]);
				move_uploaded_file($_FILES["file"]["tmp_name"],
				"../files/plans/" . $file_name);
			}
		} else {
			echo "File cannot be uploaded due to invalid file type or size";
			exit;
		}
	
		// save info to database
	  $insertSQL = sprintf("INSERT INTO planspecs (projectID, sheet, title, planTypeID, revisionDate, filename) VALUES (%s, %s, %s, %s, %s, %s)",
						   GetSQLValueString($_POST['project'], "int"),
						   GetSQLValueString($_POST['sheet'], "text"),
						   GetSQLValueString($_POST['title'], "text"),
						   GetSQLValueString($_POST['ptype'], "int"),
						   GetSQLValueString($_POST['pdate'], "date"),
						   GetSQLValueString($file_name, "text"));
	
	  mysql_select_db($database_adminConn, $adminConn);
	  $Result1 = mysql_query($insertSQL, $adminConn) or die(mysql_error());
	
	  $insertGoTo = "project.php";
	  if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
		$insertGoTo .= "&func=edit&id=" . $_POST['project'];
	  } else {
		$insertGoTo .= "?func=edit&id=" . $_POST['project'];
	  }
	  header(sprintf("Location: %s", $insertGoTo));
	}
}

mysql_select_db($database_adminConn, $adminConn);
$query_rsPlanType = "SELECT * FROM vt_plantype ORDER BY planType ASC";
$rsPlanType = mysql_query($query_rsPlanType, $adminConn) or die(mysql_error());
$row_rsPlanType = mysql_fetch_assoc($rsPlanType);
$totalRows_rsPlanType = mysql_num_rows($rsPlanType);

$colname_rsPlans = "-1";
if (isset($_GET['id'])) {
  $colname_rsPlans = $_GET['id'];
}
mysql_select_db($database_adminConn, $adminConn);
//$query_rsPlans = sprintf("SELECT * FROM planspecs WHERE id = %s", GetSQLValueString($colname_rsPlans, "int"));
$query_rsPlans = sprintf("SELECT planspecs.id AS id, projectID, sheet, title, planspecs.planTypeID AS planType, vt_plantype.planType AS plantypeTitle, revisionDate, datetimestamp, filename FROM planspecs, vt_plantype WHERE vt_plantype.id=planspecs.planTypeID and planspecs.id = %s", GetSQLValueString($colname_rsPlans, "int"));
$rsPlans = mysql_query($query_rsPlans, $adminConn) or die(mysql_error());
$row_rsPlans = mysql_fetch_assoc($rsPlans);
$totalRows_rsPlans = mysql_num_rows($rsPlans);

$colname_rsProject = "-1";
if (isset($_GET['pid'])) {
  $colname_rsProject = $_GET['pid'];
}
mysql_select_db($database_adminConn, $adminConn);
$query_rsProject = sprintf("SELECT projName FROM projects WHERE id = %s", GetSQLValueString($colname_rsProject, "int"));
$rsProject = mysql_query($query_rsProject, $adminConn) or die(mysql_error());
$row_rsProject = mysql_fetch_assoc($rsProject);
$totalRows_rsProject = mysql_num_rows($rsProject);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="605" />
<title>RFP | Administration: Project Plans &amp; Specs</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../../inc/popup.js"></script>
<style type="text/css">
/*@import url("../../inc/default.css");*/
@import url("../../inc.child.css");
@import url("../rfp.css");
</style>
<link href="../../SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<script src="../../SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
</head>

<body>
<div id="layout">
<?php require('../../inc/header.php'); ?>
<div id="content">
<div id="topslide" class="rfp">
<p><span></span></p>
</div>
<h2><span>RFP Administration</span></h2>
<div id="pspecs">
<h3><span><a href="project.php?func=edit&id=<?php echo $_GET['pid']; ?>"><span class="pname"><?php echo $row_rsProject['projName']; ?></span></a> :: Plans &amp; Specifications</span></h3>
<?php require('admnav.php'); ?>
<?php $func = strtolower($_REQUEST['func']);
switch ($func) {
	case "edit": //edit plans & specs
?>
<form name="eplans" action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" id="eplans">
<p><span class="req">*</span> <span class="note">Denotes a required field</span></p>
<?php
if ( $err != "" ) {
	echo '<div class="err"><p>Application not submitted due to the following:</p><ul>';
	echo $err;
	echo '</ul></div>';
}
?>
<table border="0" cellspacing="1" cellpadding="4" summary="form layout for plans and specifications">
<tr>
<td><span class="req">*</span> <label for="sheet">Sheet</label></td>
<td><input name="sheet" type="text" id="sheet" value="<?php echo(isset($_POST['sheet']) ? $_POST['sheet'] : $row_rsPlans['sheet']); ?>" size="35" maxlength="45" /></td>
</tr>
<tr>
  <td><span class="req">*</span> <label for="title">Title</label></td>
  <td><input name="title" type="text" id="title" value="<?php echo(isset($_POST['title']) ?$_POST['title'] : $row_rsPlans['title']); ?>" size="45" maxlength="45" /></td>
</tr>
<tr>
  <td><label for="ptype">Plan Type</label></td>
  <td><select name="ptype" id="ptype">
    <?php
do {  
?>
    <option value="<?php echo $row_rsPlanType['id']?>"<?php if (!(strcmp($row_rsPlanType['id'], $row_rsPlans['planType'])) || (isset($_POST['ptype']) && $_POST['ptype']==$row_rsPlanType['id'])) {echo "selected=\"selected\"";} ?>><?php echo $row_rsPlanType['planType']?></option>
    <?php
} while ($row_rsPlanType = mysql_fetch_assoc($rsPlanType));
  $rows = mysql_num_rows($rsPlanType);
  if($rows > 0) {
      mysql_data_seek($rsPlanType, 0);
	  $row_rsPlanType = mysql_fetch_assoc($rsPlanType);
  }
?>
  </select></td>
</tr>
<tr>
  <td><span class="req">*</span> <label for="pdate">Revision Date</label></td>
  <td><span id="sprytextfieldDate">
  <input name="pdate" type="text" id="pdate" size="20" maxlength="10" value="<?php echo(isset($_POST['pdate']) ? $_POST['pdate'] : ''); ?>" /> <span class="note">yyyy/mm/dd</span>&nbsp; <span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
</tr>
<tr>
  <td><label for="file">Upload</label></td>
  <td><a href="../files/plans/<?php echo $row_rsPlans['filename']; ?>" target="_blank">Current File</a> &nbsp;<span class="note">(Uploading will overwrite current file)</span> <input name="filename" type="hidden" id="filename" value="<?php echo $row_rsPlans['filename']; ?>" />
    <br />
<input type="hidden" name="MAX_FILE_SIZE" value="25000000" /><input name="file" type="file" class="file" id="file" size="55" /> <span class="note">(25MB Max)</span></td>
</tr>
</table>
<p class="btns">
  <input type="submit" name="submit" id="submit" value="Update" class="btn" /> <a href="project.php?func=edit&id=<?php echo $_REQUEST['pid']; ?>" class="btn"><span>Cancel</span></a>
<input name="id" type="hidden" id="id" value="<?php echo $row_rsPlans['id']; ?>" />
<input type="hidden" name="project" id="project" value="<?php echo $row_rsPlans['projectID']; ?>" />
</p>
<input type="hidden" name="MM_update" value="eplans" />
</form>
<?php
		break;
	case "del": // delete plans & specs
?>
<form name="dplans" action="<?php echo $editFormAction; ?>" method="POST" id="dplans">
<h4>Are you sure that you want to delete the following file?</h4>
<table border="0" cellspacing="1" cellpadding="4" summary="form layout for plans and specifications">
<tr>
<td><label for="sheet">Sheet</label></td>
<td><?php echo $row_rsPlans['sheet']; ?></td>
</tr>
<tr>
  <td><label for="title">Title</label></td>
  <td><a href="../files/plans/<?php echo $row_rsPlans['filename']; ?>" target="_blank"><?php echo $row_rsPlans['title']; ?></a></td>
</tr>
<tr>
  <td><label for="ptype">Plan Type</label></td>
  <td><?php echo $row_rsPlans['plantypeTitle']; ?></td>
</tr>
<tr>
  <td><label for="pdate">Revision Date</label></td>
  <td><?php echo $row_rsPlans['revisionDate']; ?></td>
</tr>
</table>
<p class="btns">
  <input type="submit" name="submit" id="submit" value="Delete" class="btn" /> <a href="project.php?func=edit&id=<?php echo $_REQUEST['pid']; ?>" class="btn"><span>Cancel</span></a>
<input type="hidden" name="project" id="project" value="<?php echo $_GET['pid']; ?>" />
<input type="hidden" name="plans" id="plans" value="<?php echo $row_rsPlans['id']; ?>" />
<input type="hidden" name="filename" id="filename" value="<?php echo $row_rsPlans['filename']; ?>" />
<input type="hidden" name="MM_delete" value="dplans" />
</p>
</form>
<?php 
		break;
	default: // add new plans & specs 
?>
<form name="nplans" action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" id="nplans">
<p><span class="req">*</span> <span class="note">Denotes a required field</span></p>
<?php
if ( $err != "" ) {
	echo '<div class="err"><p>Application not submitted due to the following:</p><ul>';
	echo $err;
	echo '</ul></div>';
}
?>
<table border="0" cellspacing="1" cellpadding="4" summary="form layout for plans and specifications">
<tr>
<td><span class="req">*</span> <label for="sheet">Sheet</label></td>
<td><input name="sheet" type="text" id="sheet" size="35" maxlength="45" value="<?php echo(isset($_POST['sheet']) ? $_POST['sheet'] : ''); ?>" /></td>
</tr>
<tr>
  <td><span class="req">*</span> <label for="title">Title</label></td>
  <td><input name="title" type="text" id="title" size="45" maxlength="45" value="<?php echo(isset($_POST['title']) ? $_POST['title'] : ''); ?>" /></td>
</tr>
<tr>
  <td><span class="req">*</span> <label for="ptype">Plan Type</label></td>
  <td><select name="ptype" id="ptype">
    <option value=""></option>
	<?php
do {  
?>
    <option value="<?php echo $row_rsPlanType['id']?>"<?php echo($row_rsPlanType['id'] == $_POST['ptype'] ? ' selected="selected"' : ''); ?>><?php echo $row_rsPlanType['planType']?></option>
    <?php
} while ($row_rsPlanType = mysql_fetch_assoc($rsPlanType));
  $rows = mysql_num_rows($rsPlanType);
  if($rows > 0) {
      mysql_data_seek($rsPlanType, 0);
	  $row_rsPlanType = mysql_fetch_assoc($rsPlanType);
  }
?>
  </select></td>
</tr>
<tr>
  <td><span class="req">*</span> <label for="pdate">Revision Date</label></td>
  <td><span id="sprytextfieldDate">
  <input name="pdate" type="text" id="pdate" size="20" maxlength="10" value="<?php echo(isset($_POST['pdate']) ? $_POST['pdate'] : ''); ?>" /> <span class="note">yyyy/mm/dd</span>&nbsp; <span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
</tr>
<tr>
  <td><span class="req">*</span> <label for="file">Upload</label></td>
  <td><input type="hidden" name="MAX_FILE_SIZE" value="25000000" /><input name="file" type="file" class="file" id="file" size="55" /> <span class="note">(25MB Max)</span></td>
</tr>
</table>
<p class="btns">
  <input type="submit" name="submit" id="submit" value="Submit" class="btn" /> <a href="project.php?func=edit&id=<?php echo $_REQUEST['pid']; ?>" class="btn"><span>Cancel</span></a>
<input type="hidden" name="project" id="project" value="<?php echo $_GET['pid']; ?>" />
</p>
<input type="hidden" name="MM_insert" value="nplans" />
</form>
<?php } // end edit vs delete vs new ?>
</div>
</div>

<?php require('../../inc/footer.php'); ?>
</div>
<script type="text/javascript">
var sprytextfieldDate = new Spry.Widget.ValidationTextField("sprytextfieldDate", "date", {isRequired:false, format:"yyyy/mm/dd"});
</script>
</body>
</html>
<?php
error_reporting(0);
mysql_free_result($rsPlanType);

mysql_free_result($rsPlans);

mysql_free_result($rsProject);
?>
