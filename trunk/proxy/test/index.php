<?php

session_start();
if ($_SESSION['loggedin'] === TRUE) {
	header('Location: http://localhost/proxy/test/loggedin.php');
}

setcookie('test1', 'test1');
setcookie('test2', 'test2');

?>
<html>
<head>
<title>Login</title>

<style type="text/css">	
@import "/test/1.css";
@import url('test/2.css');
body {
	background-image: url(img/test.jpg);
}

</style>
</head>

<body>

<h1>Login</h1>

<script type="text/javascript">

document.write('Hello from the land of JavaScript');

</script>

<p><a href="loggedin.php">Test Link</a></p>

<form method="post" action="login.php">

<table>
	<tr>
		<td>username</td>
		<td><input type="text" name="username" /></td>
	</tr>
	<tr>
		<td>password</td>
		<td><input type="password" name="password" /></td>
	</tr>
	<tr>
		<td colspan="2" align="right">
			<input type="submit" value="Login" />
		</td>
	</tr>
</table>

</form>

<fieldset>
	<legend>Test Send File</legend>

	<form enctype="multipart/form-data" action="handle-file.php" method="POST" style="display:inline;">
	<input type="hidden" name="test" value="phill" />
	<input name="userfile" type="file" />
	<input type="submit" value="Send" />
	</form>
</fieldset>

</body>
</html>