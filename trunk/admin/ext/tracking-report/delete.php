<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants

if (!is_root()) { die("You cannot delete records!"); }

// get id
$id = (int) $_GET['id'];
// delete DB record
$result = db_delete(TBL_PREFIX.TBL_RECORDS, "id='".$id."'");
// display message
echo ($result) ? "Record deleted!" : "Error deleting Log.";
?>