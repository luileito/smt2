<?php
session_start();

require '../../../config.php';

// check root user
if (!is_root()) { exit; }

if (isset($_GET['reset'])) 
{
  $ext = ext_available();
  foreach ($ext as $dir) 
  {
    $success = db_update(TBL_PREFIX.TBL_EXTS, "priority = '0'", "dir = '".$dir."'");
  }
  if ($success) { echo '<p class="warning">Reset!</p>'; }
} 
else if (isset($_GET['sort'])) 
{
  // update DB
  foreach ($_GET['sort'] as $priority => $ext) 
  {
    $success = db_update(TBL_PREFIX.TBL_EXTS, "priority = '".($priority + 1)."'", "dir = '".$ext."'");
  }
  if ($success) { echo '<p class="success">Saved!</p>'; }
}
?>