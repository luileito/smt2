<?php
$JSON = array();
// get log of group pages
if (!empty($_GET['id'])) 
{
  // set log identifier (needed in 'user.php')
  $id  = (int) $_GET['id'];
  $log = db_select(TBL_PREFIX.TBL_RECORDS." LEFT JOIN ".TBL_PREFIX.TBL_CACHE." ON ".TBL_PREFIX.TBL_RECORDS.".cache_id = ".TBL_PREFIX.TBL_CACHE.".id", 
                   TBL_PREFIX.TBL_RECORDS.".* AS record, ".TBL_PREFIX.TBL_CACHE.".* AS cache", 
                   TBL_PREFIX.TBL_RECORDS.".id = '".$id."'");
  // log fields
  $clientId       = $log['client_id'];
  $timestamp      = mask_client($clientId).'\n'.date("h:i A", strtotime($log['sess_date']));
  $htmlFile       = $log['file'];
  $url            = $log['url'];
  $viewportWidth  = (int) $log['vp_width'];
  $viewportHeight = (int) $log['vp_height'];
  $fps            = (int) $log['fps'];
  $coordsX        = array_sanitize($log['coords_x']);
  $coordsY        = array_sanitize($log['coords_y']);
  $clicksX        = implode(",", array_null($log['clicks_x']));
  $clicksY        = implode(",", array_null($log['clicks_y']));
  $hovered        = $log['hovered'];
  $clicked        = $log['clicked'];
  
  // build JavaScript object
  $JSON[] = '{"xcoords": ['.$coordsX.'], "ycoords": ['.$coordsY.'], "xclicks": ['.$clicksX.'], "yclicks": ['.$clicksY.'], "timestamp": "'.$timestamp.'", "wprev": '.$log['vp_width'].', "hprev": '.$log['vp_height'].'}';
} 

else if (!empty($_GET['pid'])) 
{
  // get page identifier
  $pgid  = (int) $_GET['pid'];
  // merge logs?
  $add = (db_option(TBL_PREFIX.TBL_CMS, "mergeCacheUrl")) ? get_common_url($pgid) : null;
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
    $coordsX[] = $cX;
    $coordsY[] = $cY;
    $weights[] = count($cX);
    //$clicksX[] = explode(",", $log['clicks_x']);
    //$clicksY[] = explode(",", $log['clicks_y']);
    $fps[]     = (int) $log['fps'];
    $hovered .= $log['hovered'];
    $clicked .= $log['clicked'];

    // build JavaScript object
    $timestamp = mask_client($log['client_id']).'\n'.date("h:i A", strtotime($log['sess_date']));
    $cdX = array_sanitize($log['coords_x']);
    $cdY = array_sanitize($log['coords_y']);
    $clX = implode(",", array_null($log['clicks_x']));
    $clY = implode(",", array_null($log['clicks_y']));
    
    // build JavaScript object
    $JSON[] = '{"xcoords": ['.$cdX.'], "ycoords": ['.$cdY.'], "xclicks": ['.$clX.'], "yclicks": ['.$clY.'], "timestamp": "'.$timestamp.'", "wprev": '.$log['vp_width'].', "hprev": '.$log['vp_height'].'}';
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
      //$clicksX[$i] = array_pad($clicksX[$i], $items+$diff, 0);
      //$clicksY[$i] = array_pad($clicksY[$i], $items+$diff, 0);
    }
  }
  $users = count($logs);
  // chek logs count to access coordinates index
  foreach ($logs as $i => $log) 
  {
    // compound single path
    foreach ($coordsX[$i] as $j => $vector) 
    {
      $sumCoordsX = 0; $sumCoordsY = 0; $sumClicksX = 0; $sumClicksY = 0;
      foreach ($weights as $k => $w) 
      {
        $sumCoordsX += (int) $coordsX[$k][$j];
        $sumCoordsY += (int) $coordsY[$k][$j];
        //$sumClicksX += (int) $clicksX[$k][$j];
        //$sumClicksY += (int) $clicksY[$k][$j];
      }
      $avgCoordsX[] = round($sumCoordsX/$users);
      $avgCoordsY[] = round($sumCoordsY/$users);
      //$avgClicksX[] = round($sumClicksX/$users);
      //$avgClicksY[] = round($sumClicksY/$users);
    }
    // only one iteration is needed
    break;
  }
  $coordsX = implode(",", $avgCoordsX);
  $coordsY = implode(",", $avgCoordsY);
  //$clicksX = implode(",", $avgClicksX);
  //$clicksY = implode(",", $avgClicksY);
  if (count($JSON) > 1) {
    $JSON[] = '{"xcoords": ['.$coordsX.'], "ycoords": ['.$coordsY.'], "xclicks": [], "yclicks": [], "avg": true, "wprev": '.$viewportWidth.', "hprev": '.$viewportHeight.'}';
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
