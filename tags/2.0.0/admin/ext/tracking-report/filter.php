<?php
//echo '<pre>'.print_r($_POST).'</pre>';exit;
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';

$defaults = array(
                    "cache_id"    => "all",
                    "client_id"   => "all",
                    "os_id"       => "all",
                    "browser_id"  => "all",
                    "newusers"    => "all",
                    "fromyear"    => date("Y") - 1,
                    "frommonth"   => 1,
                    "fromday"     => 1,
                    "fromhour"    => 0,
                    "fromminute"  => 0,
                    "toyear"      => date("Y"),
                    "tomonth"     => date("m"),
                    "today"       => date("d"),
                    "tohour"      => date("H"),
                    "tominute"    => date("i"),
                    "min"         => "(SELECT MIN(sess_time))",
                    "max"         => "(SELECT MAX(sess_time))",
                    "limit"       => (int)$_POST['limit'],
                    "groupby"     => null
                 );

// init query
$sql = "(fps > 0)";

foreach ($defaults as $key => $value) 
{
  if ($value === "all") 
  {
    // exception
    if ($key == "newusers" && isset($_POST['newusers'])) {
      $sql .= " AND ftu = '1'";
    } else if (!empty($_POST[$key])) { 
      $sql .= " AND ".$key." = '".$_POST[$key]."'";
    }
    // save?
    if (!isset($_POST['reset'])) { $_SESSION[$key] = $_POST[$key]; }
    else { unset($_SESSION[$key]); }
  } 
  else 
  {
    // create new var
    $$key = strip_tags(trim($_POST[$key]));
    // check value, otherwise use default
    if (empty($$key)) 
    {
      if (isset($_SESSION[$key])) {
        $$key = $_SESSION[$key];
        // sanitize zero values. Skip empty values on time range, but not break the loop because it should be unset if "save" is not checked
        if ($key != "min" && $key != "max" && (int)$_POST[$key] === 0 && $_POST[$key] <= $_SESSION[$key]) { $$key = 0; }
      } else {
        $$key = $value;
      }      
    }
    // save?
    if (!isset($_POST['reset'])) { $_SESSION[$key] = $$key; }
    else { unset($_SESSION[$key]); }
  }
}
 
// date range
$fromdate = date("Y-m-d H:i:s", mktime($fromhour, $fromminute, 0, $frommonth, $fromday, $fromyear) );
$todate = date("Y-m-d H:i:s", mktime($tohour, $tominute, 59, $tomonth, $today, $toyear) );
$sql .= " AND (sess_date BETWEEN '$fromdate' AND '$todate')"; 
// time range
$sql .= " AND (sess_time BETWEEN ".$min." AND ".$max.")";
// grouping
if (!empty($groupby)) { $sql .= " GROUP BY ".$groupby; }

// save or delete previous queries
if (isset($_POST['reset'])) {
  unset($_SESSION['filterquery']); 
} else {
  // anyway, save this for redirecting
  $_SESSION['filterquery'] = $sql;
  $_SESSION['limit'] = $limit;
}

//var_dump($_POST); exit;
if (isset($_POST['download'])) {
	include 'download.php';
} else {
	header("Location: ./");
}
/*
echo "<br><br>".$sql."<br><br>";
$records = db_select_all(TBL_PREFIX.TBL_RECORDS, "id,client_id,cache_id,os_id,browser_id,ftu,sess_date,sess_time", $sql);
var_dump($records);
*/
?>