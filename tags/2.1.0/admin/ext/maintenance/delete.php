<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';
// now you have access to all CMS API

// define a helper function
function delete_cache_log($id)
{
  // delete cached file
  $cacheQuery = "id='".$id."'";
  $page = db_select(TBL_PREFIX.TBL_CACHE, "file", $cacheQuery);
  if (is_file(CACHE_DIR.$page['file'])) {
    unlink(CACHE_DIR.$page['file']);
  }
  // now delete cache log
  $success = db_delete(TBL_PREFIX.TBL_CACHE, $cacheQuery);

  return ($success) ? 'Deleted!' : 'Error!';
}

// delete single cached log
if (!empty($_GET['pid']))
{
  $id = (int) $_GET['pid'];
  $msg = delete_cache_log($id);
  // here we are using the SetupCMS.deleteTrackingButtons()
  echo '{"success":'.(bool)$msg.',"response":"'.$msg.'"}';
}
// bulk deletion of cached logs
else if (!empty($_POST))
{
  $ids = explode(",", $_POST['id']);
  foreach ($ids as $id)
  {
    $msg = delete_cache_log($id);
  }
  // if everything went ok, display the $_notifyMsg["SAVED"] string. Otherwise, use a custom error message.
  notify_request("orphanlogs", (bool)$msg, $msg);
}
?>