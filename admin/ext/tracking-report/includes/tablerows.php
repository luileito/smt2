<?php
// needed for async calls to this file
session_start();
/* Defining a relative path to smt2 root in this script is a bit tricky,
 * because this file can be called both from Ajax and regular HTML requests. 
 */
$base = realpath(dirname(__FILE__).'/../../../../');
require $base.'/config.php';
// use ajax settings
require dirname(__FILE__).'/settings.php';

// get ajax data
if (!empty($_GET['page'])) { $page = $_GET['page']; }
if (!empty($_GET['show'])) { $show = $_GET['show']; }
// set query limits
$start = $page * $show - $show;
$limit = "$start,$show";
// is JavaScript enabled?
if (isset($_GET[$resetFlag])) { $limit = $page*$show; }
// get records
$records = db_select_all(TBL_PREFIX.TBL_RECORDS, "*", "1 ORDER BY sess_date DESC, client_id LIMIT $limit");
// if there are no more records, display message
if (!$records) {
  die('<!--'.$noMoreText.'-->'); 
}

// show pretty dates over timestamps if PHP >= 5.2.0
if (check_systemversion("php", "5.2.0")) {
  $usePrettyDate = true;
  require_once INC_PATH.'sys/class.prettydate.php';
}
// call this function once, using session data for Ajax request
$ROOT = is_root();
// dump (smt) records  
foreach ($records as $i => $r) 
{
  $cssClass = ($i%2 == 0) ? "odd" : "even";
  // display a start on first time visitors
  $ftu = ($r['ftu']) ? ' class="ftu"' : null;
  // use pretty date?
  $displayDate = ($usePrettyDate) ? 
  '<acronym title="'.prettyDate::getStringResolved($r['sess_date']).'">'.$r['sess_date'].'</acronym>' : 
  $r['sess_date'];
  // wait for very recent visits
  $receivingData = (time() - strtotime($r['sess_date']) < 5);  
  
  // create list item
  $tablerow .= '<tr class="'.$cssClass.'">'.PHP_EOL;
  $tablerow .= ' <td'.$ftu.'>'.substr(md5($r['client_id']), -8, 8).'</td>'.PHP_EOL;
  $tablerow .= ' <td>'.$displayDate.'</td>'.PHP_EOL;
  $tablerow .= ' <td>'.$r['sess_time'].'</td>'.PHP_EOL;
  $tablerow .= ' <td>'.PHP_EOL;
  if (!$receivingData) {
    $tablerow .= '  <a href="track.php?id='.$r['id'].'&amp;api=swf" rel="external" title="use the interactive Flash drawing API">SWF</a>'.PHP_EOL; 
    $tablerow .= ' | <a href="track.php?id='.$r['id'].'&amp;api=js" rel="external" title="use the old JavaScript drawing API">JS</a>'.PHP_EOL;
  } else {
    $tablerow .= '<em>please wait...</em>';
  }
  $tablerow .= ' </td>'.PHP_EOL;
  $tablerow .= ' <td>'.PHP_EOL;
  if (!$receivingData) {
    $tablerow .= '  <a href="analyze.php?id='.$r['id'].'" rel="external" title="analyze this log">analyze</a>'.PHP_EOL;
    if ($ROOT) {
      $tablerow .= ' | <a href="delete.php?id='.$r['id'].'" title="delete this log" class="del">delete</a>'.PHP_EOL;
    }
  } else {
    $tablerow .= '<em>receiving data</em>';
  }
  $tablerow .= ' </td>'.PHP_EOL;
  $tablerow .= '</tr>'.PHP_EOL;
}
  
echo $tablerow;
// check both normal and async (ajax) requests
if ($start + $show < db_records()) {
  $displayMoreButton = true;
} else {
  echo '<!--'.$noMoreText.'-->'.PHP_EOL;
}
?>