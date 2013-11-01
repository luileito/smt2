<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';
// now you have access to all CMS API

include INC_DIR.'header.php';
// only root user can perform certain operations
$ROOT = is_root();

// load maintenance configuration
require 'config.php';
?>

<h1 id="orphanlogs">Orphan cache logs</h1>
<?=check_notified_request("orphanlogs")?>

<?php
// get cache logs id
$cached = db_select_all(TBL_PREFIX.TBL_CACHE, "id", 1);
$cached = array_flatten($cached);
// get records cache id
$records = db_select_all(TBL_PREFIX.TBL_RECORDS, "cache_id", 1);
$records = array_flatten($records);
// compute difference
$diff = array_diff($cached, $records);
$num = count($diff);
if ($num > 0) {
?>

  <p>There are <?=$num?> cached pages that are no longer referenced on <code><?=TBL_PREFIX.TBL_RECORDS?></code> table,
  so it is safe to delete them.</p>

  <table cellpadding="10" cellspacing="1">
  <thead>
  <tr>
  <th>url</th>
  <th>date</th>
  <?php
  if ($ROOT) {
    echo '<th>action</th>';
  }
  ?>
  </tr>
  </thead>
  
  <?php
  // build query
  $sql = "id ='".array_shift($diff)."'";
  if (count($diff) > 0) {
    foreach ($diff as $value) {
      $sql .= " OR id='".$value."'";
    }
  }
  // select orphan logs
  $cache = db_select_all(TBL_PREFIX.TBL_CACHE, "*", $sql);
  
  $rows = "";
  foreach ($cache as $log) {
    $rows .= '<tr>'.PHP_EOL;
    $rows .= '<td class="pl pr"><a href="'.$log['url'].'" rel="external" title="'.$log['title'].'">'.trim_text($log['title']).'</a></td>'.PHP_EOL;
    $rows .= '<td class="pl pr">'.$log['saved'].'</td>'.PHP_EOL;
    if ($ROOT) {
      $rows .= '<td class="pl pr"><a class="del" href="delete.php?pid='.$log['id'].'">delete</a></td>'.PHP_EOL;
    }
    $rows .= '</tr>'.PHP_EOL;
  }
  ?>

  <tbody>
  <?=$rows?>
  </tbody>
  </table>
  
  <?php
  if ($ROOT) {
  ?>
  <form method="post" action="delete.php">
    <fieldset>
      <input type="hidden" name="id" value="<?=implode(",", array_diff($cached, $records))?>" />
      <input type="submit" class="button round delete conf" value="Delete all orphan logs" />
    </fieldset>
  </form>
  <?php
  }
}
else
{
  echo '<p>By now there are no orphan cache logs.</p>';
}
?>



<h1 id="backup" class="mt">Backup database</h1>
<?=check_notified_request("backup")?>

<?php
// check dir
if (!is_dir(BACKUPDIR)) 
{
  if (!mkdir(BACKUPDIR)) {
    echo display_text($_displayType["ERROR"],
                      BACKUPDIR.' does not exist and it could not be created.
                       You must create <strong>'.BACKUPDIR.'</strong> dir or specify another directory to save database backups in <code>admin/ext/maintenance/index.php</code> file.'
                     );
  }
}

clearstatcache();
// ensure full access to backup dir (at least apache/IIS)
if (!is_writable(BACKUPDIR)) {
  $perms = substr(decoct( fileperms(BACKUPDIR) ), 2);
  if (($perms != "775" || $perms != "777") && !chmod(BACKUPDIR, 0775)) 
  {
    echo display_text($_displayType["ERROR"],
                      'Settings permissions to <strong>'.BACKUPDIR.'</strong> failed.
                       You must set write permissions (either 775 or 777) to that directory manually.'
                     );
  }
}
if ($ROOT) {
?>
<p>
  You can either <a href="backup.php?task=1">download</a> your <code><?=DB_NAME?></code> database,
  <a href="backup.php?task=2">back it up</a> on the server,
  or just <a href="backup.php?task=0">look at the current data</a>.
</p>
<p>
  Note that in all cases the whole database will be dumped in SQL format.
  If you only want to export the interactions logs from the <code><?=TBL_PREFIX.TBL_RECORDS?></code>,
  please go to <a href="../admin-logs/">the admin logs</a> section instead.
</p>
<?php
}

// check previously saved logs
if ($handle = opendir(BACKUPDIR))
{
  $files = array();
  while (false !== ($file = readdir($handle))) {
    // look for available module extensions
    if ($file != "." && $file != ".." && is_file(BACKUPDIR.'/'.$file)) {
      $files[] = $file;
    }
  }
  closedir($handle);
  
  
  $num = count($files);
  if ($num > 0) {
    $rows = "";
    foreach ($files as $file) {
      $rows .= '<li><a href="'.basename(BACKUPDIR).'/'.$file.'">'.$file.'</a></li>'.PHP_EOL;
    }
?>

  <p>You have <?=$num?> previous database backups. Here they are listed by their creation date:</p>

  <ul class="pl">
  <?=$rows?>
  </ul>
  
<?php
  } else { //there are no backups
    echo '<em>There are no previous database backups.</em>';
  }
} // end dir handle
?>


<?php include INC_DIR.'footer.php'; ?>
