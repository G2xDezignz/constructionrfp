<div id="admnav">
<?php if ($_SESSION['rfp_adm_MM_UserGroup'] == '3' && basename($_SERVER['PHP_SELF'])=="home.php") { ?>
<p><a href="user_edit.php?func=edit&id=<?php echo$_SESSION['rfp_adm_MM_UserID']; ?>" class="btn">User Profile</a> <a href="logout.php" class="btn">Logout</a></p>
<?php } else { ?>
<p><?php if (basename($_SERVER['PHP_SELF'])=="allsubprops.php") { ?><a href="subusers.php" class="btn">Sub Profile(s)</a> <?php } ?><?php if (basename($_SERVER['PHP_SELF'])!="home.php") { ?><a href="home.php" class="btn">Project List</a> <?php } ?> <a href="logout.php" class="btn">Logout</a></p>
<?php } //endif ?>
</div>
