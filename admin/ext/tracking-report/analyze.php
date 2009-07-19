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
  // current log
  $id = (int) $_GET['id'];
  // shorcuts to table names
  $r = TBL_PREFIX.TBL_RECORDS;
  $c = TBL_PREFIX.TBL_CACHE;
  $b = TBL_PREFIX.TBL_BROWSERS;
  $o = TBL_PREFIX.TBL_OS;
  // get log data
  $sql = db_select($r." LEFT JOIN ".$c." ON ".$r.".cache_id = ".$c.".id LEFT JOIN ".$b." ON ".$r.".browser_id = ".$b.".id LEFT JOIN ".$o." ON ".$r.".os_id = ".$o.".id", 
                   $r.".* AS record, ".$c.".* AS cache, ".$b.".name AS browser, ".$o.".name AS os", 
                   TBL_PREFIX.TBL_RECORDS.".id = '".$id."'");
                   
  if (!$sql) { die("Error"); }
  
  $clientId = $sql['client_id'];
  
  include './includes/rawlog-header.php';
  
  if (db_option(TBL_PREFIX.TBL_CMS, "displayGoogleMap")) {
    include './includes/location.php';
  }
  
  include './includes/rawlog-widget.php';
  ?>
  </div><!-- end rawlog -->

  <?php
  include './includes/clickpath.php'; 
  ?>


<?php include INC_PATH.'inc/footer.php'; ?>