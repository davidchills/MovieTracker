<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$loginObj = new USERS();
	$loginObj->login($_POST['username'], $_POST['password']);
}
else { $_SESSION = array(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>User Login</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<?php print MOVIETRACKER::importCSS(array('jquery.ui','base')); ?>
<?php print MOVIETRACKER::importJS(array('jquery','jquery.ui','dhfCombined')); ?>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#submitButton").button();
});
</script>
</head>
<body>

<div id="container">
	<div id="wrap">
		<br />

		<fieldset class="adminEditBlock">
		<legend align="top">Login Credentials</legend>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="loginForm" name="loginForm" autocomplete="off">

		<label for="username" class="width75">Username</label>
		<input type="text" id="username" name="username" value="" required="required" maxlength="255" placeholder="user@domain.com" class="width150" />

		<br /><br />

		<label for="password" class="width75">Password</label>
		<input type="password" id="password" name="password" required="required" maxlength="100" placeholder="abc123" value="" class="width150" />

		<br /><br />

		<span class="width75">&nbsp;</span>
		<input type="submit" id="submitButton" value="Submit Login" class="width150" />

		</form>
		</fieldset>
		<br />
	</div>
</div>
</body>
</html>
