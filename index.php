<?php require_once('Connections/webConn.php'); ?>
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
mysql_select_db($database_webConn, $webConn);
$query_rsProjects = "SELECT id, projName, projState, projCity, projSummary, projDetail, UNIX_TIMESTAMP(closeDate) AS closeDate FROM projects WHERE projStatus='O' AND closeDate >= NOW() ORDER BY projState, projName ASC";
$rsProjects = mysql_query($query_rsProjects, $webConn) or die(mysql_error());
$row_rsProjects = mysql_fetch_assoc($rsProjects);
$totalRows_rsProjects = mysql_num_rows($rsProjects);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RFP | Plan Room</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="inc/popup.js"></script>
<style type="text/css">
/*@import url("inc/default.css");*/
@import url("inc/child.css");
@import url("rfp/rfp.css");
</style>
</head>

<body>
<div id="layout">
<?php require('inc/header.php'); ?>
<div id="content">
<div id="topslide">
<img src="inc/blueprint_slide.jpg" width="961" height="230" alt="Plan Room" /> </div>
<div id="content_layout" class="allspan">
<div id="maincontent-rspan">
<h2><span>Request for Proposals for Subcontractors &amp; Suppliers</span></h2>
<?php if ($totalRows_rsProjects == 0) { // Show if recordset empty ?>
  <p>There are currently no developments accepting a <em>Request for Proposal</em> (<em>RFP</em>). Please check back often as this may change.</p>
<?php } // Show if recordset empty ?>
<?php if ($totalRows_rsProjects > 0) { // Show if recordset not empty ?>
  <div id="rfplist">
    <p style="margin-top:-.5em;margin-bottom:1.5em;">We are currently accepting <em>Requests for Proposal</em>, or <em>RFP</em>s, for the following:</p>
    <div class="locale">
      <table border="0" cellspacing="1" cellpadding="4" summary="list of current projects">
        <caption><?php echo $row_rsProjects['projState']; ?></caption>
        <thead>
          <tr>
            <th scope="col">Project Name</th>
            <th scope="col">Summary</th>
            <th scope="col">Location</th>
            <th scope="col">Closes</th>
            </tr>
        </thead>
        <tbody>
          <?php do { ?>
            <tr>
              <td class="t1"><a href="rfp/project.php?id=<?php echo $row_rsProjects['id']; ?>"><?php echo $row_rsProjects['projName']; ?></a></td>
              <td class="t2"><?php echo $row_rsProjects['projSummary']; ?></td>
              <td class="t3"><?php echo $row_rsProjects['projCity']; ?>, <?php echo $row_rsProjects['projState']; ?></td>
              <td class="t4"><?php echo date('m-d-Y',$row_rsProjects['closeDate']); ?></td>
            </tr>
            <?php } while ($row_rsProjects = mysql_fetch_assoc($rsProjects)); ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php } // Show if recordset not empty ?>
<!-- end content_layout -->
</div></div>
<?php require('inc/footer.php'); ?>
</div>
</body>
</html>
