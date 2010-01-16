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
  $entryAvgX[] = $arr[0];
  $exitAvgX[] = $arr[ count($arr)-1 ];
  $minX[] = min($cleanX[$i]); // faster than iterating over $arr
  $maxX[] = max($cleanX[$i]);
  // vertical components
  $entryAvgY[] = $coordsY[$i][0];
  $exitAvgY[] = $coordsY[$i][ count($coordsY[$i])-1 ];
  $minY[] = min($cleanY[$i]); // faster than iterating over $coordsY
  $maxY[] = max($cleanY[$i]);
  // get euclidean distances
  $distCoords[] = convert_points($arr, $coordsY[$i], true);
}

// use ponderation for average values when working on coordinates arrays
$centroidX = weighted_avg($cleanX, $weights);
$centroidY = weighted_avg($cleanY, $weights);
$distAvg = weighted_avg($distCoords, $weights);

// kinematics study
foreach ($distCoords as $i => $arr) {
  $stop = 0;
  foreach($arr as $k => $distance) {
    if ($distance > 0) { continue; }
    ++$stop;
  }
  $stops[] = $stop/count($arr);
}

// clicks study
foreach ($clicksX as $i => $arr) {
	$cleanClicksX = array();
	$cleanClicksY = array();
	// split drag and drop traces
	foreach ($arr as $k => $v) {
		//echo $v."-";
		//echo ($v>0)."-";
		if ($v > 0 && $clicksY[$i][$k] > 0) {
			// does next coordinate exist
			if ($v == $arr[$k+1] && $clicksY[$i][$k] == $clicksY[$i][$k+1]) {
				$cleanClicksX[] = $v;
				$cleanClicksY[] = $clicksY[$i][$k];
			}
		}
	}
	// normalize
  $x[] = array_unique($cleanClicksX);
  $y[] = array_unique($cleanClicksY);
	// sum
  $clicksSumX += count($x[$i]);
  $clicksSumY += count($y[$i]);
}

//$cleanClicksX = array_unique($cleanClicksX); var_dump($cleanClicksX);

$clicksAvgX = $clicksSumX/count($x);
$clicksAvgY = $clicksSumY/count($y);
// scroll/viewport study
foreach ($vpWidth as $i => $width) {
  $vpAvgWidth[] = $width;
  $vpAvgHeight[] = $vpHeight[$i];
}
$amplitudeAvgX = array_avg($maxX) - array_avg($minX);
$amplitudeAvgY = array_avg($maxY) - array_avg($minY);
$scrollAvgX = round($amplitudeAvgX / array_avg($vpAvgWidth), 2);
$scrollAvgY = round($amplitudeAvgY / array_avg($vpAvgHeight), 2);
?>

<table cellpadding="10" cellspacing="1">
  <thead>
      <tr>
        <th>session time</th>
        <th>distance (px)</th>
        <th>activity (%)</th>
        <th>number of clicks</th>
        <th>entry point</th>
        <th>exit point</th>
        <th>amplitude (px)</th>
        <th>scroll reach (%)</th>
        <th>centroid</th>
      </tr>
  </thead>
  <tbody>
  <?php
  $list  = '<tr class="even">'.PHP_EOL;
  // log data  
  $list .= '<td>'.PHP_EOL;
  $list .= array_avg($time);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_avg($distAvg);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= 1 - array_avg($stops);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= round(($clicksAvgX + $clicksAvgY) / 2);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_avg($entryAvgX).", ".array_avg($entryAvgY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_avg($exitAvgX).", ".array_avg($exitAvgY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= $amplitudeAvgX.", ".$amplitudeAvgY;
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= $scrollAvgX.", ".$scrollAvgY;
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= array_avg($centroidX).", ".array_avg($centroidY);
  $list .= '</td>'.PHP_EOL;
  $list .= '</tr>'.PHP_EOL;
  echo $list;
  ?>
  </tbody>    
</table>