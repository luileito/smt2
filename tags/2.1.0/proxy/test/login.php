<?php

session_start();

$username = $_POST['username'];
$password = $_POST['password'];

if (!empty($username) && !empty($password)) {
	$_SESSION['loggedin'] = TRUE;
	$_SESSION['username'] = $username;
	header('Location: http://localhost/proxy/test/loggedin.php');
}
else{
	header('Location: http://localhost/proxy/test/index.php');
}

?>