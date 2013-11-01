<?php
//echo '<pre>'.print_r($_POST).'</pre>';exit;
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';

$defaults = array(
                    "cache_id"    => "all",
                    "domain_id"   => "all",
                    "url"         => "all",
                    "client_id"   => "all",
                    "os_id"       => "all",
                    "browser_id"  => "all",
                    "fps"         => "all",                    
                    "ftu"         => "all",
                    "ip"          => "all",
                    //"from"        => date("Y/m/d H:i", strtotime("last year")),
                    "fromyear"    => date("Y") - 1,
                    "frommonth"   => 1,
                    "fromday"     => 1,
                    "fromhour"    => 0,
                    "fromminute"  => 0,
                    //"to"          => date("Y/m/d H:i"),
                    "toyear"      => date("Y"),
                    "tomonth"     => date("m"),
                    "today"       => date("d"),
                    "tohour"      => date("H"),
                    "tominute"    => date("i"),
                    "mintime"     => "(SELECT MIN(sess_time))",
                    "maxtime"     => "(SELECT MAX(sess_time))",
                    "limit"       => (int)$_POST['limit'],
                    "groupby"     => null
                 );

// init query
$sql = "(fps > 0)";

foreach ($defaults as $key => $value) 
{
  if ($value === "all") 
  {
    // exception (it's a checkbox)
    if ($key == "ftu" && isset($_POST['ftu'])) {
      $sql .= " AND ftu = '1'";
    } else if (!empty($_POST[$key])) { 
      $sql .= " AND ".$key." = '".$_POST[$key]."'";
    }
    // save?
    if (!isset($_POST['reset']) && isset($_POST[$key])) { $_SESSION[$key] = $_POST[$key]; }
    else if (isset($_SESSION[$key])) { unset($_SESSION[$key]); }
  } 
  else 
  {
    // create new var
    if (isset($_POST[$key])) { ${$key} = strip_tags(trim($_POST[$key])); }
    // check value, otherwise use default
    if ( empty(${$key}) ) 
    {
      if ( isset($_SESSION[$key]) && isset($_POST[$key]) ) {
        ${$key} = $_SESSION[$key];
        // sanitize zero values. Skip empty values on time range, but not break the loop because it should be unset if "save" is not checked
        if ($key == "mintime" || $key == "maxtime") {
          ${$key} = $value;
        } else if ( (int)$_POST[$key] === 0 && $_POST[$key] <= $_SESSION[$key] ) { 
          ${$key} = 0; 
        }
        //OLD usage: if ($key != "mintime" && $key != "maxtime" && (int)$_POST[$key] === 0 && $_POST[$key] <= $_SESSION[$key]) { ${$key} = 0; }
      } else {
        ${$key} = $value;
      }      
    }
    // save?
    if (!isset($_POST['reset'])) { $_SESSION[$key] = ${$key}; }
    else if (isset($_SESSION[$key])) { unset($_SESSION[$key]); }
  }
}

// parse date
$from = $_POST['from'];
$to   = $_POST['to'];
$pattern = "/\d{2}\/\d{2}\/\d{4}\s\d{2}:\d{2}\s(a|p)m{1}/i";
if ( (!empty($from) && !preg_match($pattern,$from)) || (!empty($to) && !preg_match($pattern,$to)) ) {
  notify_request("mine", false, "Please use this date format: <em>mm/dd/yyyy hh:mm (a|p)m</em> &rarr; example: <em>07/23/2009 11:30 am</em>");
}

// date range
$sfrom  = (!empty($from)) ? strtotime($from) : strtotime("last year");
$sto    = (!empty($to)) ? strtotime($to) : strtotime("now"); // +1 day?
$fromdate = date("Y-m-d H:i:s", $sfrom);
$todate   = date("Y-m-d H:i:s", $sto);
$sql .= " AND (sess_date BETWEEN '$fromdate' AND '$todate')";

// time range
$sql .= " AND (sess_time BETWEEN ".$mintime." AND ".$maxtime.")";

// grouping
if (!empty($groupby)) {
  $sql .= " GROUP BY ".$groupby;
} else {
  unset($_SESSION['groupby']);
}

// save or delete previous queries
if (isset($_POST['reset'])) {
  unset($_SESSION['filterquery'], $_SESSION['groupby'], $_SESSION['from'], $_SESSION['to']);
} else {
  // anyway, save this for redirecting
  $_SESSION['filterquery'] = $sql;
  $_SESSION['limit'] = $limit;
  // save dates
  $_SESSION['from'] = date("m/d/Y h:i a", $sfrom);
  $_SESSION['to'] = date("m/d/Y h:i a", $sto);
}

if (isset($_POST['download'])) {
	include 'download.php';
} else {
	header("Location: ./");
}
?>
