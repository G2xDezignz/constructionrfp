<?php
if ($_SESSION['rfp_adm_MM_UserGroup'] <> '0') {
?>
<div id="alist">
<ul>
<?php if ($_SESSION['rfp_adm_MM_UserGroup'] == '1' || $_SESSION['rfp_adm_MM_UserGroup'] == '2') { ?>
<li><a href="users.php"<?php if (basename($_SERVER['PHP_SELF'])=="users.php" || basename($_SERVER['PHP_SELF'])=="user_edit.php") echo ' class="here"'; ?>><span>Admin Users</span></a></li>
<?php } ?>
<?php if (basename($_SERVER['PHP_SELF'])<>"user_edit.php") { ?>
<li><a href="subusers.php"<?php if (basename($_SERVER['PHP_SELF'])=="subusers.php" || basename($_SERVER['PHP_SELF'])=="subuser_edit.php") echo ' class="here"'; ?>>Sub Profile(s)</a></li>
<?php } ?>
<?php if ($_SESSION['rfp_adm_MM_UserGroup'] == '1' || $_SESSION['rfp_adm_MM_UserGroup'] == '2') { ?>
<li><a href="vt_projectstate.php"<?php if (basename($_SERVER['PHP_SELF'])=="vt_projectstate.php") echo ' class="here"'; ?>><span>Project State(s) / Location</span></a></li>
<li><a href="vt_plantype.php"<?php if (basename($_SERVER['PHP_SELF'])=="vt_plantype.php") echo ' class="here"'; ?>><span>Plan Type(s)</span></a></li>
<li><a href="vt_proposaltype.php"<?php if (basename($_SERVER['PHP_SELF'])=="vt_proposaltype.php") echo ' class="here"'; ?>><span>Proposal Type(s)</span></a></li>
<?php } ?>
</ul>
</div>
<?php } ?>