<?php
// transform mouse coordinates in a real PHP array
$xcoords = explode(",", $coordsX);
$ycoords = explode(",", $coordsY);

if (!count(array_sanitize($xcoords))) die("This user did not move the mouse on the page.");

// transform arrays in a single points array
$pointArray = convert_points($xcoords, $ycoords);

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
  if ($size > 5) {
    $clusterSize[] = $size;
    $clusterX[] = round($cluster->avgPoint->x);
    $clusterY[] = round($cluster->avgPoint->y);
  }
}
?>