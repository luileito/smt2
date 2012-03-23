<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';
// now you have access to all (smt) API functions and constants

// insert custom elements on HEAD section
add_head('<link rel="stylesheet" type="text/css" href="styles/analyze.css" />');
add_head('<script type="text/javascript" src="'.SWFOBJECT.'"></script>');

include INC_DIR.'header.php';
?>


<p>&larr; <a href="./">Back to tracking report</a></p>
    
<div id="rawlog">
    
  <h1 class="heading center">Log Data Analysis</h1>
  <?php
  if (isset($_GET['id'])) 
  {
    // common var for other modules
    $id = (int) $_GET['id']; 
    // shorcuts to table names
    $r = TBL_PREFIX.TBL_RECORDS;
    $c = TBL_PREFIX.TBL_CACHE;
    $b = TBL_PREFIX.TBL_BROWSERS;
    $o = TBL_PREFIX.TBL_OS;
    // get log data
    $log = db_select($r." LEFT JOIN ".$c." ON ".$r.".cache_id = ".$c.".id LEFT JOIN ".$b." ON ".$r.".browser_id = ".$b.".id LEFT JOIN ".$o." ON ".$r.".os_id = ".$o.".id", 
                     $r.".* AS record, ".$c.".* AS cache, ".$b.".name AS browser, ".$o.".name AS os", 
                     TBL_PREFIX.TBL_RECORDS.".id = '".$id."'");
                     
    if (!$log) { 
      die('<strong>Error</strong>: User log #'.$id.' was not found on database.'); 
    }
    
    // user globals
    $clientId = $log['client_id'];
    $time[] = $log['sess_time'];
    $vpWidth[] = $log['vp_width'];
    $vpHeight[] = $log['vp_height'];
    $cX = explode(",", $log['coords_x']);
    $cY = explode(",", $log['coords_y']);
    $coordsX[] = $cX;
    $coordsY[] = $cY;
    /*$clickCoords = get_click_coordinates($cX,$cY,$log['clicks']);
    $clicksX[] = $clickCoords['x'];
    $clicksY[] = $clickCoords['y'];*/
    $clicks[] = $log['clicks'];
    $hovered = $log['hovered'];
    $clicked = $log['clicked'];
    
    include './includes/rawlog-client.php';
    
    if (db_option(TBL_PREFIX.TBL_CMS, "displayGoogleMap")) {
      $IP = $log['ip'];
      include './includes/rawlog-location.php';
    }
  }
  else if (isset($_GET['pid'])) 
  {
    $page = (int) $_GET['pid'];
    // merge logs
    $add = (db_option(TBL_PREFIX.TBL_CMS, "mergeCacheUrl")) ? get_cache_common_url($page) : null;
    $logs = db_select_all(TBL_PREFIX.TBL_RECORDS, "*", "cache_id = '".$page."'".$add);
  }
  else if (isset($_GET['cid'])) 
  {
    $clientId = $_GET['cid'];
    // skip visualization items from log data
    $logs = db_select_all(TBL_PREFIX.TBL_RECORDS, "*", "client_id = '".$clientId."'");
    // the same user could come from different locations, so don't include the raw-location file
  }
  else if (isset($_GET['ip']))
  {
    // skip visualization items from log data
    $logs = db_select_all(TBL_PREFIX.TBL_RECORDS, "*", "ip = '".$_GET['ip']."'");
    if (db_option(TBL_PREFIX.TBL_CMS, "displayGoogleMap")) {
      $IP = $_GET['ip'];
      include './includes/rawlog-location.php';
    }
  }
  
  
  // now compute grouped metrics
  if (isset($_GET['cid']) || isset($_GET['pid']) || isset($_GET['ip']))
  {
    if (!$logs) { die("Error retrieving logs."); }
    
    $sampleSize = db_option(TBL_PREFIX.TBL_CMS, "maxSampleSize");
    if ($sampleSize > 0)
      $keys = array_rand($logs, $sampleSize);

    // group metrics
    $hovered = ""; $clicked = "";
    foreach ($logs as $i => $log)
    {
      if( isset($_GET['pid']) && (isset($keys) && !in_array($i, $keys)) ) continue;
      
      $time[] = $log['sess_time'];
      $vpWidth[] = $log['vp_width'];
      $vpHeight[] = $log['vp_height'];
      $cX = explode(",", $log['coords_x']);
      $cY = explode(",", $log['coords_y']);
      $coordsX[] = $cX;
      $coordsY[] = $cY;
      /*$clickCoords = get_click_coordinates($cX,$cY,$log['clicks']);
      $clicksX[] = $clickCoords['x'];
      $clicksY[] = $clickCoords['y'];*/
      $clicks[] = $log['clicks'];
      $hovered .= $log['hovered'];
      $clicked .= $log['clicked'];
    }
  }  

  include './includes/rawlog-widget.php';
  include './includes/rawlog-coords.php';
  ?>
  </div><!-- end rawlog -->

  <?php
  if (isset($_GET['id']) || isset($_GET['cid'])) 
  {
    include './includes/clickpath.php';
  }
  ?>


<?php include INC_DIR.'footer.php'; ?>
