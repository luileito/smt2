<?php
session_start();
require './config.php';

include INC_DIR.'doctype.php';
?>

<head>
  <?php include INC_DIR.'header-base.php'; ?>
</head>

<body>
  <?php include INC_DIR.'loginform.php'; ?>
</body>

</html>