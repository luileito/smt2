<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';
// now you have access to all CMS API

// only root user can backup the database
if (!is_root()) { die_msg($_loginMsg["NOT_ALLOWED"]); }

require SYS_DIR.'class.db.backup.php';

// load maintenance configuration
require 'config.php';

$backup = new MySQL_Backup();
$backup->server   = DB_HOST;
$backup->username = DB_USER;
$backup->password = DB_PASSWORD;
$backup->database = DB_NAME;
// backup all tables on 'backup' dir
$backup->tables       = array();
$backup->backup_dir   = BACKUPDIR;
$backup->fname_format = 'Ymd-His';

$task = (int) $_GET['task'];
$run = $backup->Execute($task);
if (!$run)
{
  $output = $backup->error;
}
else
{
  $output = 'Operation completed successfully at <strong>' . date('H:i:s') . '</strong><em> (Local Server Time)</em>.';
  
  if ($task == MSB_SAVE)
  {
    notify_request("backup", (bool)$run, $output);
  }
  else if ($task == MSB_STRING)
  {
    echo $output.'<br /><pre>'.$run.'</pre>';
  }
}
?>
