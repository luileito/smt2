<?php
session_start();
/**
 * To be implemented on next (smt) releases.
 * @date 12/September/2009 
 */ 
require '../../config.php';
// is root logged?
if (!is_root()) { die_msg($_loginMsg["NOT_ALLOWED"]); }

include INC_DIR.'doctype.php'
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
$UPGRADED = false;

// define helper function
function update($table, $arrValue) 
{
  global $UPGRADED;

  // was upgraded before?
  if (!db_select($table, "id", "name = '".$arrValue[1]."'")) {
    $sql  = "INSERT INTO ".$table." (type,name,value,description)";
    $sql .= " VALUES ('".$arrValue[0]."','".$arrValue[1]."','".$arrValue[2]."','".$arrValue[3]."')";
    db_query($sql);
    
    $UPGRADED = true;
  }
}

// put common visualization options on CMS table
$opts = array(
                array(CMS_TYPE,   "maxSampleSize",      0, "Number of logs to replay/analyze simultaneously (0 means no limit). If your database has a lot of records for the same URL, you can take into account only a small subset of logs."),
                // disabled by default
                array(CMS_CHOICE, "mergeCacheUrl",      0, "Merges all logs that have the same URL. Useful when grouping records by page ID, and one wants to analyze all common URLs."),
                array(CMS_CHOICE, "displayWidgetInfo",  0, "Display hover and click frequency for each interacted DOM element."),
                array(CMS_CHOICE, "refreshOnResize",    0, "Reload visualization page on resize window &ndash; <em>use with care</em>, as on some browsers the resize event will fire endlessly."),
                array(CMS_CHOICE, "displayWidgetInfo",  0, "Display hover and click frequency for most interacted DOM elements.")
             );
// update CMS options table
foreach ($opts as $arrValue) {
  update(TBL_PREFIX.TBL_CMS, $arrValue);
}

// put new javascript options on JS table
$opts = array(
                array(CMS_CHOICE, "loadNextTrail",  0, "Try to load more trails automatically (if available) for the current tracked user.")
             );
// update JS options table
foreach ($opts as $arrValue) {
  update(TBL_PREFIX.TBL_JSOPT, $arrValue);
}

// display message
$msg = ($UPGRADED) ? "smt2 has been upgraded!" : "You already have the latest version.";
?>

<p><?=$msg?> <a href="../">Go to admin page</a>.</p>

</div><!-- end global div -->

</body>

</html>