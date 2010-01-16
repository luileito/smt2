<?php
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
  $htmlFile       = $log['file'];
  $url            = $log['url'];
  $viewportWidth  = (int) $log['vp_width'];
  $viewportHeight = (int) $log['vp_height'];
  $fps            = (int) $log['fps'];
  $coordsX        = $log['coords_x'];
  $coordsY        = $log['coords_y'];
  $clicksX        = $log['clicks_x'];
  $clicksY        = $log['clicks_y'];
  $hovered        = $log['hovered'];
  $clicked        = $log['clicked'];                
} 

else if (!empty($_GET['pid'])) 
{
  // get page identifier
  $pid  = (int) $_GET['pid'];
  $logs = db_select_all(TBL_PREFIX.TBL_RECORDS, 
                        "id,sess_time,vp_width,vp_height,fps,coords_x,coords_y,clicks_x,clicks_y,hovered,clicked", 
                        "cache_id = '".$pid."'");
  // group metrics
  foreach ($logs as $i => $log) 
  {
    $viewportWidth[]  = (int) $log['vp_width'];
    $viewportHeight[] = (int) $log['vp_height'];
    $coordsX[] = explode(",", $log['coords_x']);
    $coordsY[] = explode(",", $log['coords_y']);
    //$clicksX[] = explode(",", $log['clicks_x']);
    //$clicksY[] = explode(",", $log['clicks_y']);
    //$weights[] = (int) round($log['sess_time']);
    $weights[] = count($coordsX[$i]);
    $fps[]     = (int) $log['fps'];
    $hovered .= $log['hovered'];
    $clicked .= $log['clicked'];
  }
  // now create the average user path
  $fps = ceil(array_avg($fps));
  $viewportWidth  = ceil(array_avg($viewportWidth));
  $viewportHeight = ceil(array_avg($viewportHeight));
  // preprocess: pad all vectors
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
  
  $cache = db_select(TBL_PREFIX.TBL_CACHE, "file,url", "id = '".$pid."'");
  $htmlFile = $cache['file']; 
  $url = $cache['url'];
} 

else { 
  die('No tracking data'); 
}
?>