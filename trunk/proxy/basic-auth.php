<?php
require_once(dirname(__FILE__) . '/lib/PHPProxy.class.php');

if ($_POST['username'] == NULL):
?>
<html>
<head>
<title>Authentication Required</title>

<link href="css/style.css" type="text/css" rel="stylesheet" />

</head>

<body>

<h1>Authentication Required</h1>

<p>Authentication is required before you can access <strong><?php echo base64_decode($_GET[URL_PARAM_NAME]) ?></strong>.</p>

<p>The realm given is: <strong><?php echo htmlentities($_REQUEST['realm']) ?></strong></p>

<form method="post" action="basic-auth.php">
<input type="hidden" name="<?php echo URL_PARAM_NAME ?>" value="<?php echo $_GET[URL_PARAM_NAME] ?>" />

<fieldset>
	<legend>Login</legend>

	<table>
		<tr>
			<td><label for="username">Username</label></td>
			<td><input type="text" name="username" id="username" /></td>
		</tr>
		<tr>
			<td><label for="password">Password</label></td>
			<td><input type="password" name="password" id="password" /></td>
		</tr>
		<tr>
			<td colspan="2" align="right">
				<input type="submit" value="Login" />
			</td>
		</tr>
	</table>

</fieldset>

</form>

</body>
</html>
<?
else:

$url = $_POST[URL_PARAM_NAME];
$username = base64_encode($_POST['username']);
$password = base64_encode($_POST['password']);

header('Location: index.php?' . URL_PARAM_NAME . "=$url&proxy_username=$username&proxy_password=$password");

endif;
?>