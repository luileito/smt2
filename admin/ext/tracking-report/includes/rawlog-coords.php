<?php
// coords study
foreach ($coordsX as $i => $arr)
{
  // using $arr is the same as using $coordsX[$i];
  $weights[] = count($arr);
  // centroids should be computed discarding null distances
  $cleanX[] = array_unique($arr);
  $cleanY[] = array_unique($coordsY[$i]);
  // horizontal components
  $entryX[] = $arr[0];
  $exitX[] = !empty($arr[count($arr)-1]) ? $arr[ count($arr) - 1 ] : $arr[ count($arr) - 2 ];
  $minX[] = min($cleanX[$i]); // faster than iterating over $arr
  $maxX[] = max($cleanX[$i]);
  $amplitudeX[] = $maxX[$i] - $minX[$i];
  $scrollX[] = $amplitudeX[$i] / $vpWidth[$i];
  // vertical components
  $entryY[] = $coordsY[$i][0];
  //$exitY[] = !empty($coordsY[$i][count($coordsY[$i])-1]) ? $coordsY[$i][ count($coordsY[$i]) - 1 ] : $coordsY[$i][ count($coordsY[$i]) - 2 ];
  $exitY[] = $coordsY[$i][ count($coordsY[$i]) - 1 ];
  $minY[] = min($cleanY[$i]); // faster than iterating over $coordsY
  $maxY[] = max($cleanY[$i]);
  $amplitudeY[] = $maxY[$i] - $minY[$i];
  $scrollY[] = $amplitudeY[$i] / $vpHeight[$i];
  
  // get euclidean distances
  $distCoords[] = convert_points($arr, $coordsY[$i], true);
  // split X and Y components
  $distCoordsX = array(); $distCoordsY = array();
  $maxCount = count($arr) - 1;
  foreach ($arr as $j => $value) {
    if ($j >= $maxCount) break;
    $distCoordsX[] = abs($coordsX[$i][$j] - $coordsX[$i][$j + 1]);
    $distCoordsY[] = abs($coordsY[$i][$j] - $coordsY[$i][$j + 1]);
  }
  // save average values
  $distAvgX[] = array_avg($distCoordsX);
  $distAvgY[] = array_avg($distCoordsY);

  $pathLengthX[] = array_sum($distCoordsX);
  $pathLengthY[] = array_sum($distCoordsY);
}
// use ponderation for average values when working on coordinates arrays
$centroidX = weighted_avg($cleanX, $weights);
$centroidY = weighted_avg($cleanY, $weights);
$distAvg = weighted_avg($distCoords, $weights);
//$pathLengthX = weighted_avg($pathLengthX, $weights);
//$pathLengthY = weighted_avg($pathLengthY, $weights);

// kinematics study
$stops = array(); $pathLength = array();
foreach ($distCoords as $i => $arr) {
  $stop = 0;
  foreach($arr as $k => $distance) {
    if ($distance > 0) continue;
    ++$stop;
  }
  $stops[] = $stop/count($arr);
  // compute also the mouse path length
  $pathLength[] = array_sum($arr);
}

// clicks study
$clicksSum = array();
foreach ($clicksX as $i => $arr) {
  $clicksSum[] = count_clicks($clicksX[$i], $clicksY[$i]);
}
$clicksAvg = array_avg($clicksSum);
$clicksSD = array_sd($clicksSum);
?>

<h3 class="mt">
  Interaction metrics
</h3>

<table class="cms" cellpadding="10" cellspacing="1">
  <thead>
      <tr>
        <!--<th>statistic</th>-->
        <th>time (s)</th>
        <th>activity (%)</th>
        <th>clicks</th>
        <th>distance (px)</th>
        <th>length (px)</th>
        <th>amplitude (px)</th>
        <th>scroll reach (%)</th>
        <th>entry point</th>
        <th>exit point</th>
        <th>centroid</th>
      </tr>
  </thead>
  <tbody>
  <?php
  // average values of log data
  $list  = '<tr class="even">'.PHP_EOL;
  //$list .= '<td><abbr title="Sample Mean">&mu;</abbr></td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= matrix_avg($time);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= 1 - matrix_avg($stops);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= $clicksAvg;
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  //$list .= matrix_avg($distAvg);
  $list .= matrix_avg($distAvgX).",<br />".matrix_avg($distAvgY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  //$list .= array_avg($pathLength);
  $list .= array_avg($pathLengthX).",<br />".array_avg($pathLengthY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= matrix_avg($amplitudeX).",<br />".matrix_avg($amplitudeY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= matrix_avg($scrollX).",<br />".matrix_avg($scrollY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= matrix_avg($entryX).",<br />".matrix_avg($entryY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= matrix_avg($exitX).",<br />".matrix_avg($exitY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= matrix_avg($centroidX).",<br />".matrix_avg($centroidY);
  $list .= '</td>'.PHP_EOL;
  $list .= '</tr>'.PHP_EOL;
  
  // standard deviations of log data
  $list .= '<tr class="odd">'.PHP_EOL;
  //$list .= '<td><abbr title="Sample Standard Deviation">&sigma;</abbr></td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_sd($time);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_sd($stops);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= $clicksSD;
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  //$list .= matrix_sd($distCoords);
  $list .= array_sd($distAvgX).",<br />".array_sd($distAvgY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  //$list .= array_sd($pathLength);
  $list .= array_sd($pathLengthX).",<br />".array_sd($pathLengthY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_sd($amplitudeX).",<br />".array_sd($amplitudeY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_sd($scrollX).",<br />".array_sd($scrollY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_sd($entryX).",<br />".array_sd($entryY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_sd($exitX).",<br />".array_sd($exitY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= matrix_sd($cleanX).",<br />".matrix_sd($cleanY);
  $list .= '</td>'.PHP_EOL;
  $list .= '</tr>'.PHP_EOL;

  echo $list;
  ?>
  </tbody>    
</table>

<div class="small">
  <p>
  Notes:
  </p>
  <ol class="ml pl">
    <li>First row is <abbr title="Sample Mean">&mu;</abbr>, while second row is <abbr title="Sample Standard Deviation">&sigma;</abbr>.</li>
    <li>Values for activity and scroll reach are actually reported as per-unit: 0 < value < 1.</li>
    <li>Comma-separated values denotes a column vector (X and Y components).</li>
  </ol>
</div>