<?php
session_start();
/**
 * To be implemented on next (smt) releases.
 * @date 12/September/2009 
 */ 
require '../../config.php';
// is root logged?
if (!is_root()) { die_msg($_loginMsg["NOT_ALLOWED"]); }

include INC_DIR.'doctype.php';

$UPGRADED = false;
?>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title><?=CMS_TITLE?> | upgrade</title>
  <link rel="stylesheet" type="text/css" href="<?=ADMIN_PATH?>css/install.css" />
</head>

<body>

<div id="global">

<h1>(smt) simple mouse tracking upgrade</h1>

<?php
// alter records table
$res = db_query("SHOW COLUMNS FROM ".TBL_PREFIX.TBL_RECORDS." LIKE 'ip'");
if (!mysql_num_rows($res)) {
  $sql  = "ALTER TABLE `".TBL_PREFIX.TBL_RECORDS."` ADD `ip` VARCHAR(15) NOT NULL AFTER `ftu`, ";
  $sql .= "MODIFY `client_id` CHAR(32) NOT NULL";
  db_query($sql);
  $UPGRADED = true;
}
// in any case, allow recording more time
$res = db_query("ALTER TABLE `".TBL_PREFIX.TBL_RECORDS."` MODIFY `sess_time` FLOAT(7,2) unsigned NOT NULL");

// check if clicks should be updated
$res = db_query("SHOW COLUMNS FROM ".TBL_PREFIX.TBL_RECORDS." LIKE 'clicks'");
if (!mysql_num_rows($res)) {
  // convert previous clicks to the UNIPEN format
  $logs = db_select_all(TBL_PREFIX.TBL_RECORDS, "id,clicks_x", 1); // there is no need to parse clicks_y for this conversion
  if ($logs) {
    $info = array();
    foreach ($logs as $log) {
      $oldclicks = explode(",", $log['clicks_x']);
      $clicks = array();
      foreach ($oldclicks as $cx) {
        $clicks[] = (!empty($cx)) ? 1 : 0;
      }
      $info[] = array(
                        "id"     => $log['id'],
                        "clicks" => implode(",", $clicks),
                     );
    }
    // create new column
    $sql = "ALTER TABLE `".TBL_PREFIX.TBL_RECORDS."` ADD `clicks` MEDIUMTEXT NOT NULL AFTER `fps`";
    db_query($sql);
    // and update old DB records with the new values
    foreach ($info as $log) {
      db_update(TBL_PREFIX.TBL_RECORDS, "clicks = '".$log['clicks']."'", "id=".$log['id']);  
    }
    // then remove old columns
    $sql = "ALTER TABLE ".TBL_PREFIX.TBL_RECORDS." DROP COLUMN `clicks_x`, DROP COLUMN `clicks_y`";
    db_query($sql);
  }
  $UPGRADED = true;
}

// define helper function
function update_cms($table, $fields, $values, $condition)
{
  global $UPGRADED;

  // was upgraded before?
  if (!db_select($table, "id", $condition)) {
    db_insert($table, $fields, $values);
    $UPGRADED = true;
  }
}

// put common visualization options on CMS table
$opts = array(
                array(CMS_TYPE,   "maxSampleSize",      0, "Number of logs to replay/analyze simultaneously (0 means no limit). If your database has a lot of records for the same URL, you can take into account only a certain subset of logs."),
                // disabled by default
                array(CMS_CHOICE, "mergeCacheUrl",      0, "Merges all logs that have the same URL. Useful when grouping records by page ID, and one wants to analyze all common URLs."),
                array(CMS_CHOICE, "refreshOnResize",    0, "Reload visualization page on resizing the browser window."),                
                array(CMS_CHOICE, "displayWidgetInfo",  0, "Display hover and click frequency for each interacted DOM element."),
                array(CMS_CHOICE, "displayGoogleMap",   0,  "If you typed a valid Google Maps key on your <em>config.php</em> file, the client location will be shown on a map when analyzing the logs."),
                array(CMS_CHOICE, "enableDebugging",    0, "Turn on PHP strict mode and work with JS src files instead of minimized ones.")
             );
// update CMS options table
foreach ($opts as $arrValue) {
  update_cms(
              TBL_PREFIX.TBL_CMS,
              "type,name,value,description",
              "'".$arrValue[0]."','".$arrValue[1]."','".$arrValue[2]."','".$arrValue[3]."'",
              "name = '".$arrValue[1]."'"
            );
}

// put new javascript options on JS table
$opts = array(
                array(CMS_CHOICE, "loadNextTrail",  0, "Try to load more trails automatically (if available) for the current tracked user.")
             );
// update JS options table
foreach ($opts as $arrValue) {
  update_cms(
              TBL_PREFIX.TBL_JSOPT,
              "type,name,value,description",
              $arrValue[0]."','".$arrValue[1]."','".$arrValue[2]."','".$arrValue[3],
              "name = '".$arrValue[1]."'"
            );
}

// display message
$msg = ($UPGRADED) ? "smt2 has been upgraded!" : "You already have the latest upgrades.";
?>

<p><?=$msg?> <a href="../">Go to admin page</a>.</p>

</div><!-- end global div -->

</body>

</html>
