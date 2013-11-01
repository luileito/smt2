<?php
require_once SYS_DIR.'class.kmeans.php';
// transform mouse coordinates in a real PHP array
$xcoords = explode(",", $coordsX);
$ycoords = explode(",", $coordsY);

//echo count($xcoords)." points (duplicated ones will be removed)\n";

// transform arrays in a single points array
$pointArray = convert_points($xcoords, $ycoords);

/* We can do as many clusters as (to the extreme) the sample points size, 
 * but use the rule of thumb: k ~ sqrt(n/2)
 */
$n = count($pointArray);
$k = ceil(sqrt($n/2));
echo $n." points and ".$k." clusters.\n";

$prevVar = 0;
// compute K-means
for ($i = 1; $i <= $n; ++$i)
{
  ini_set('max_execution_time', 30);
  $km = new KMeans($pointArray, $k);
  $km->initKatsavounidis();
  $km->maxIterations = 5;
  
  $c = $km->distributeOverClusters();

  $clusterVarX = array();
  $clusterVarY = array();
  // store points
  foreach ($c as $cluster) 
  { 
    $clusterVarX[] = $cluster->variance->x;
    $clusterVarY[] = $cluster->variance->y;
  }
  //var_dump($clusterVarX, $clusterVarY);
  $jointVar = array_sum($clusterVarX) * array_sum($clusterVarY);
  //$jointVar = (array_sum($clusterVarX)/count($clusterVarX) + array_sum($clusterVarY)/count($clusterVarY) ) / 2;
  
  if ($prevVar == 0) $prevVar = $jointVar;
  
  $explainedVar = ($prevVar - $jointVar) / $prevVar;
  
  //echo preg_replace("#\.#", ",", $jointVar). " ";
  echo $jointVar. " ";
  
  $prevVar = $jointVar;
}
echo  "\n\n";
?>
