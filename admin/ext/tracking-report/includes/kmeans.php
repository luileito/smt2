<?php
// transform mouse coordinates in a real PHP array
$xcoords = explode(",", $coordsX);
$ycoords = explode(",", $coordsY);
// initialize points array
$pointArray = Array();
// transform arrays in a single points array
foreach ($xcoords as $index => $value) 
{
  $p = new Point($value, $ycoords[$index]); 
  // check if next point exists 
  if ($xcoords[$index + 1] === null) { break; }
  // ok
  $q = new Point($xcoords[$index + 1], $ycoords[$index + 1]);
  // append point to the points array, discarding null distances
  if ($p->getDistance($q) > 0) { $pointArray[] = $p; }
}

/* We can do as many clusters as (to the extreme) the sample points size, 
 * but use the rule of thumb: k ~ sqrt(n/2)
 */
$n = count($pointArray);
$maxClusters = (int) ceil(sqrt($n/2));

// compute K-means (distributeOverClusters2 uses Katsavounidis initialization technique)
$c = distributeOverClusters($maxClusters, $pointArray);

// store points
foreach ($c as $cluster) 
{
  $size = count($cluster->points);
  if ($size > 0) {
    $clusterSize[] = $size;
    $clusterX[] = round($cluster->avgPoint->x);
    $clusterY[] = round($cluster->avgPoint->y);
  }
}
?>