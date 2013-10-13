<?php
session_start();
session_destroy();

require '../../config.php';

// delete login cookie
if (isset($_COOKIE['smt-login'])) {
  // delete user cookie
  setcookie("smt-login", null, time(), ABS_PATH);
}

// redirect to root dir, where user authentication will prompt
header("Location: ".ABS_PATH);
?>