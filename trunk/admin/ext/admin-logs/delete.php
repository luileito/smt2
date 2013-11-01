<?php
if (empty($_POST)) exit;

// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';

// only the root user can delete logs
if (!is_root()) { die_msg($_loginMsg["NOT_ALLOWED"]); }

if (isset($_GET['id'])) 
{
  $logQuery = "id='".(int) $_GET['id']."'";
}
else if (isset($_GET['cid']))
{
  $logQuery = "client_id='".$_GET['cid']."'";
}
else if (isset($_GET['pid'])) 
{
  $pageId = (int) $_GET['pid'];
  $logQuery = "cache_id='".$pageId."'";
  // delete cached file
  $cacheQuery = "id='".$pageId."'";
  $page = db_select(TBL_PREFIX.TBL_CACHE, "file", $cacheQuery);
  if (is_file(CACHE_DIR.$page['file'])) {
    unlink(CACHE_DIR.$page['file']);
  }
  // now delete cache log
  db_delete(TBL_PREFIX.TBL_CACHE, $cacheQuery);
}

// now delete selected logs
$result = db_delete(TBL_PREFIX.TBL_RECORDS, $logQuery);
// display message
$response = ($result) ? "Deleted!" : "Error!";
// return JSON data
echo '{"success":'.(bool)$result.',"response":"'.$response.'"}';
?>