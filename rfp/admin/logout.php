<?php
// *** Logout the current user.
$logoutGoTo = ".";
if (!isset($_SESSION)) {
  session_start();
}
$_SESSION['rfp_adm_MM_Username'] = NULL;
$_SESSION['rfp_adm_MM_UserGroup'] = NULL;
$_SESSION['func'] = NULL;
unset($_SESSION['rfp_adm_MM_Username']);
unset($_SESSION['rfp_adm_MM_UserGroup']);
unset($_SESSION['func']);
if ($logoutGoTo != "") {header("Location: $logoutGoTo");
exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>RFP | Administration: Log Out</title>
</head>

<body>
<p>Logging out of the RFP Administration...</p>
</body>
</html>