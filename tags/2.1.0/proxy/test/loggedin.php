<?php

session_start();

if ($_SESSION['loggedin'] !== TRUE) {
	header('Location: index.php');
	die();
}

?>
<html>
<head>
<title>Logged In</title>
</head>

<body>

<h1>Logged In</h1>

<p>Congratulations, you are logged in as: <strong><?php echo $_SESSION['username']; ?></strong></p>.

</body>
</html>