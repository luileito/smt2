<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';

// protect from GET attempts
if (!is_root()) { die("You cannot delete records!"); }

if (isset($_GET['id'])) 
{
  $table = TBL_PREFIX.TBL_RECORDS;
  $query = "id='".(int) $_GET['id']."'";
} 
else if (isset($_GET['pid'])) 
{
  $pageId = (int) $_GET['pid'];
  $table = TBL_PREFIX.TBL_CACHE;
  $query = "id='".$pageId."'";
  // delete cached file
  $page = db_select(TBL_PREFIX.TBL_CACHE, "file", "id='".$pageId."'");
  if (is_file(CACHE.$page['file'])) { unlink(CACHE.$page['file']); }
} 
else if (isset($_GET['cid'])) 
{
  $table = TBL_PREFIX.TBL_RECORDS;
  $query = "client_id='".$_GET['cid']."'";
}

// now delete row from $table
$result = db_delete($table, $query);
// display message
echo ($result) ? "Deleted!" : "Error while deleting.";
?>