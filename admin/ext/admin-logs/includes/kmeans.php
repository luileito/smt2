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

if (!count(array_sanitize($xcoords)) && !$_POST['xhr']) die("<strong>Error</strong>: No mouse data.");

// transform arrays in a single points array
$pointArray = convert_points($xcoords, $ycoords);

// We can do as many clusters as (to the extreme) the sample points size, 
// so better use the rule of thumb: k ~ sqrt(n/2)
$n = count($pointArray);
$k = ceil(sqrt($n/2));

$km = new KMeans($pointArray, $k);
$km->initKatsavounidis();
$km->doCluster();
foreach ($km->clusters as $i => $cluster) 
{     
  $size = count($cluster);
  // exclude singleton clusters
  if ($size < 2) continue;

  $clusterSize[] = $size;
  // round cluster centers for better drawing
  $clusterAvgX[] = round($km->centroids[$i][0]);
  $clusterAvgY[] = round($km->centroids[$i][1]);
}

// check Flash client request
if ($_POST['xhr']) {
  echo json_encode(
    array(
      "xclusters" => $clusterAvgX,
      "yclusters" => $clusterAvgY,
      "sizes"     => $clusterSize
    )
  );
}
?>
