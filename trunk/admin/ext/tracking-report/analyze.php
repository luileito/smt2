<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants

// insert custom css
add_head('<link rel="stylesheet" type="text/css" href="styles/analyze.css" />');
add_head('<script type="text/javascript" src="'.SWFOBJECT.'"></script>');

include INC_PATH.'inc/header.php';
?>


<p>&larr; <a href="./">Back to tracking index</a></p>
    
<div id="rawlog">
    
  <h1 class="heading center">Raw Log Data</h1>
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
                     
    if (!$log) { die("User log #$id was not found on database."); }
    
    // user globals
    $clientId = $log['client_id'];
    $time[] = $log['sess_time'];
    $vpWidth[] = $log['vp_width'];
    $vpHeight[] = $log['vp_height'];
    $coordsX[] = explode(",", $log['coords_x']);
    $coordsY[] = explode(",", $log['coords_y']);
	 $clicksX[] = explode(",", $log['clicks_x']);
	 $clicksY[] = explode(",", $log['clicks_y']);
    $hovered = $log['hovered'];
    $clicked = $log['clicked'];
    
    include './includes/rawlog-header.php';
    
    if (db_option(TBL_PREFIX.TBL_CMS, "displayGoogleMap")) {
      include './includes/location.php';
    }
  } 
  else if (isset($_GET['pid'])) 
  {
    $page = (int) $_GET['pid'];
    $logs = db_select_all(TBL_PREFIX.TBL_RECORDS, "id,sess_time,vp_width,vp_height,coords_x,coords_y,clicks_x,clicks_y,hovered,clicked",
                          "cache_id = '".$page."'");
    
    if (!$logs) { die("Error retrieving logs."); }
    //var_dump($logs); exit;
  } 
  else if (isset($_GET['cid'])) 
  {
    $clientId = $_GET['cid'];
    // skip visualization items from log data
    $logs = db_select_all(TBL_PREFIX.TBL_RECORDS, "id,sess_time,vp_width,vp_height,coords_x,coords_y,clicks_x,clicks_y,hovered,clicked",
                          "client_id = '".$clientId."'");
  }
  
  // compute grouped metrics
  if (isset($_GET['cid']) || isset($_GET['pid'])) 
  {
    foreach ($logs as $log) {
      $time[] = $log['sess_time'];
      $vpWidth[] = $log['vp_width'];
      $vpHeight[] = $log['vp_height'];
      $coordsX[] = explode(",", $log['coords_x']);
      $coordsY[] = explode(",", $log['coords_y']);
      $clicksX[] = explode(",", $log['clicks_x']);
      $clicksY[] = explode(",", $log['clicks_y']);
      $hovered .= $log['hovered'];
      $clicked .= $log['clicked'];
    }
  }  

  include './includes/rawlog-widget.php';
  include './includes/rawlog-coords.php';
  ?>
  </div><!-- end rawlog -->

  <?php
  if (isset($_GET['id']) || isset($_GET['cid'])) {
    include './includes/clickpath.php';
  }
  ?>


<?php include INC_PATH.'inc/footer.php'; ?>