<?php
session_start();
?>
<html>
<head>
<title>Error</title>

<link href="css/style.css" type="text/css" rel="stylesheet" />

</head>

<body>

<h1>Error</h1>

<p>There was an error accessing the page you requested.</p>

<p>The error was:</p>

<blockquote class="connect-error"><?php echo $_SESSION['error'] ?></blockquote>

</body>
</html>
