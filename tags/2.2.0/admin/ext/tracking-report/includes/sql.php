<?php
$JSON = array();
// get log of group pages
if (!empty($_GET['id'])) 
{
  // set log identifier (needed in 'user.php')
  $id  = (int) $_GET['id'];
  $log = db_select(TBL_PREFIX.TBL_RECORDS." LEFT JOIN ".TBL_PREFIX.TBL_CACHE." ON ".TBL_PREFIX.TBL_RECORDS.".cache_id = ".TBL_PREFIX.TBL_CACHE.".id", 
                   TBL_PREFIX.TBL_RECORDS.".*, ".TBL_PREFIX.TBL_CACHE.".*", 
                   TBL_PREFIX.TBL_RECORDS.".id = '".$id."'");

  // log fields
  $clientId       = $log['client_id'];
  $timestamp      = mask_client($clientId).'\n'.date("h:i A", strtotime($log['sess_date']));
  $htmlFile       = $log['file'];
  $url            = $log['url'];
  $viewportWidth  = (int) $log['vp_width'];
  $viewportHeight = (int) $log['vp_height'];
  $fps            = (int) $log['fps'];
  $clicks         = $log['clicks'];
  $coordsX        = $log['coords_x'];
  $coordsY        = $log['coords_y'];
  $hovered        = $log['hovered'];
  $clicked        = $log['clicked'];
  
  // build JavaScript object
  $JSON[] = '{"xcoords": ['.$coordsX.'], "ycoords": ['.$coordsY.'], "clicks": ['.$clicks.'], "timestamp": "'.$timestamp.'", "wprev": '.$log['vp_width'].', "hprev": '.$log['vp_height'].'}';
} 

else if (!empty($_GET['pid'])) 
{
  // get page identifier
  $pgid  = (int) $_GET['pid'];
  // merge logs?
  $add = (db_option(TBL_PREFIX.TBL_CMS, "mergeCacheUrl")) ? get_cache_common_url($pgid) : null;
  $logs = db_select_all(TBL_PREFIX.TBL_RECORDS, "*", "cache_id = '".$pgid."'".$add);
  
  $sampleSize = db_option(TBL_PREFIX.TBL_CMS, "maxSampleSize");
  if ($sampleSize > 0)
    $keys = array_rand($logs, $sampleSize);
    
  // group metrics
  $hovered = ""; $clicked = "";
  foreach ($logs as $i => $log) 
  {
    if (isset($keys) && !in_array($i, $keys)) continue;
    
    $viewportWidth[]  = (int) $log['vp_width'];
    $viewportHeight[] = (int) $log['vp_height'];
    $cX = explode(",", $log['coords_x']);
    $cY = explode(",", $log['coords_y']);
    $cl = explode(",", $log['clicks']);
    $weights[] = count($cX);
    $coordsX[] = $cX; // we'll need'em later
    $coordsY[] = $cY; //
    $clicks[]  = $cl; //
    $fps[]    = (int) $log['fps'];
    $hovered .= $log['hovered'];
    $clicked .= $log['clicked'];

    // build JavaScript object
    $timestamp = mask_client($log['client_id']).'\n'.date("h:i A", strtotime($log['sess_date']));
    $JSON[] = '{"xcoords": ['.$log['coords_x'].'], "ycoords": ['.$log['coords_y'].'], "clicks": ['.$log['clicks'].'], "timestamp": "'.$timestamp.'", "wprev": '.$log['vp_width'].', "hprev": '.$log['vp_height'].'}';
  }
  
  // now compute the average user path -----------------------------------------
  $fps = ceil(array_avg($fps));
  $viewportWidth  = ceil(array_avg($viewportWidth));
  $viewportHeight = ceil(array_avg($viewportHeight));
  // preprocess: pad all mouse vectors
  $maxWeight = max($weights);
  foreach ($weights as $i => $w) 
  {
    $items = count($coordsX[$i]);
    $diff = $maxWeight - $items;
    if ($diff > 0 ) {
      $coordsX[$i] = array_pad($coordsX[$i], $items+$diff, 0);
      $coordsY[$i] = array_pad($coordsY[$i], $items+$diff, 0);
      $clicks[$i] = array_pad($clicks[$i], $items+$diff, 0);
    }
  }
  $users = count($logs);
  // chek logs count to access coordinates index
  foreach ($logs as $i => $log) 
  {
    // compound single path
    foreach ($coordsX[$i] as $j => $vector) 
    {
      $sumCoordsX = 0; $sumCoordsY = 0; $sumClicks = 0;
      foreach ($weights as $k => $w) 
      {
        $sumCoordsX += (int) $coordsX[$k][$j];
        $sumCoordsY += (int) $coordsY[$k][$j];
        $sumClicks += (int) $clicks[$k][$j];
      }
      $avgCoordsX[] = round($sumCoordsX/$users);
      $avgCoordsY[] = round($sumCoordsY/$users);
      $avgClicks[] = round($sumClicks/$users);
    }
    // only one iteration is needed
    break;
  }
  $coordsX = implode(",", $avgCoordsX);
  $coordsY = implode(",", $avgCoordsY);
  $clicks = implode(",", $avgClicks);
  if (count($JSON) > 1) {
    $JSON[] = '{"xcoords": ['.$coordsX.'], "ycoords": ['.$coordsY.'], "clicks": ['.$clicks.'], "avg": true, "wprev": '.$viewportWidth.', "hprev": '.$viewportHeight.'}';
  }
  
  // set page that matches the given cache id (via GET)
  $cache = db_select(TBL_PREFIX.TBL_CACHE, "file,url", "id = '".$pgid."'");
  $htmlFile = $cache['file']; 
  $url = $cache['url'];
} 

else { 
  die_msg("No tracking data");
}
?>
