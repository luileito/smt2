<?php
// check Flash client request
if ($_POST['xhr']) {
  require '../../../../config.php';
  $xcoords = json_decode($_POST['xdata']);
  $ycoords = json_decode($_POST['ydata']);
} else {
  // transform mouse coordinates in a real PHP array
  $xcoords = explode(",", $coordsX);
  $ycoords = explode(",", $coordsY);
}
// load KMeans class
require SYS_DIR.'class.kmeans.php';

if (!count(array_sanitize($xcoords)) && !$_POST['xhr']) die("<strong>Error</strong>: No mouse data.");

// transform arrays in a single points array
$pointArray = convert_points($xcoords, $ycoords);

/* We can do as many clusters as (to the extreme) the sample points size, 
 * but use the rule of thumb: k ~ sqrt(n/2)
 */
$n = count($pointArray);
$k = (int) ceil(sqrt($n/2));

$km = new KMeans($k, $pointArray);
$km->initKatsavounidis();
// 10 iterations are enough if Katsavounidis initialization is used
$km->maxIterations = 10;
$c = $km->distributeOverClusters();

// store points
foreach ($c as $cluster) 
{     
  $size = count($cluster->points);
  // exclude singleton clusters
  if ($size < 2) continue;

  $clusterSize[] = $size;
  // round cluster centers for better drawing
  $clusterAvgX[] = round($cluster->avgPoint->x);
  $clusterAvgY[] = round($cluster->avgPoint->y);
  $clusterVarX[] = $cluster->variance->x;
  $clusterVarY[] = $cluster->variance->y;
}

// check Flash client request
if ($_POST['xhr']) 
{
  $response  = '{';
  $response .=    '"xclusters":['  . implode(",", $clusterAvgX) . '],';
  $response .=    '"yclusters":['  . implode(",", $clusterAvgY) . '],';
  $response .=    '"xvariances":[' . implode(",", $clusterVarX) . '],';
  $response .=    '"yvariances":[' . implode(",", $clusterVarY) . '],';
  $response .=    '"sizes":['      . implode(",", $clusterSize) . ']';
  $response .= '}';
  
  echo json_encode($response);
}
?>