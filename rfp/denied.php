<?php
//initialize the session
if (!isset($_SESSION)) {
  session_start();
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
  unset($_SESSION['rfp_MM_Username']);
  unset($_SESSION['rfp_MM_UserGroup']);
  unset($_SESSION['PrevUrl']);
	
  $logoutGoTo = "login.php";
  if ($logoutGoTo) {
    header("Location: $logoutGoTo");
    exit;
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RFP | Contractor Access</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link href="/inc/default.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="/inc/popup.js"></script>
<style type="text/css">
/*@import url("../inc/default.css");*/
@import url("../inc/child.css");
@import url("rfp.css");
fieldset { border-color: transparent; }
.allspan { min-height: 360px; }
</style>
</head>

<body>
<div id="layout">
<?php require('../inc/header.php'); ?>
<div id="content">
<div id="topslide" class="rfp">
<p><span></span></p>
</div>
<div id="content_layout" class="allspan">
<h2><span>Contractor Access</span></h2>
<div id="login">
<h3><span>Access Denied</span></h3>
<p style="margin:2.5em 10px 4em 10px;">This <em>Contractor Access</em> section of this web site requires secured access and it appears as though you do not have the appropriate permissions. If you believe this to be in error, please try to <a href="<?php echo $logoutAction ?>">login</a> again or contact the system administrator.</p>
<div class="warn">
<p>WARNING: This section of the Construction RFP web site is for authorized personnel only.<br />
All violators will be prosecuted to the full extent of the law.</p>
</div>
</div>
</div>
</div>
<?php require('../inc/footer.php'); ?>
</div>
</body>
</html>