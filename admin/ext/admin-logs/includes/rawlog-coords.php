<?php
// coords study
foreach ($mouseTracks as $track)
{
  $mf = new MouseFeat($track);
  
  $allClicks[]    = $mf->numClicks;
  $allActivity[]  = $mf->activity;  
  $allTime[]      = $mf->time;
  $allEntryX[]    = $mf->entry['x'];
  $allEntryY[]    = $mf->entry['y'];  
  $allExitX[]     = $mf->exit['x'];
  $allExitY[]     = $mf->exit['y'];
  $allRangeX[]    = $mf->range['x'];
  $allRangeY[]    = $mf->range['y'];
  $allScrollX[]   = $mf->scrollReach['x'];
  $allScrollY[]   = $mf->scrollReach['y'];
  $allCenX[]      = $mf->centroid['x'];
  $allCenY[]      = $mf->centroid['y'];
  $allLenX[]      = $mf->trackLen['x'];
  $allLenY[]      = $mf->trackLen['y'];
  $allDistX[]     = $mf->distance['x'];  
  $allDistY[]     = $mf->distance['y'];  
}
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
        <th>range (px)</th>
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
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allTime);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allActivity);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allClicks);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allDistX).",<br />".array_avg($allDistY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .=    array_avg($allLenX).",<br />".array_avg($allLenY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allRangeX).",<br />".array_avg($allRangeY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allScrollX).",<br />".array_avg($allScrollY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allEntryX).",<br />".array_avg($allEntryY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allExitX).",<br />".array_avg($allExitY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_avg($allCenX).",<br />".array_avg($allCenY);
  $list .= '</td>'.PHP_EOL;
  $list .= '</tr>'.PHP_EOL;
  
  // standard deviations of log data
  $list .= '<tr class="odd">'.PHP_EOL;
  //$list .= '<td><abbr title="Sample Standard Deviation">&sigma;</abbr></td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allTime);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allActivity);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allClicks);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allDistX).",<br />".array_sd($allDistY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allLenX).",<br />".array_sd($allLenY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allRangeX).",<br />".array_sd($allRangeY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allScrollX).",<br />".array_sd($allScrollY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allEntryX).",<br />".array_sd($allEntryY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allExitX).",<br />".array_sd($allExitY);
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>' .PHP_EOL;
  $list .=    array_sd($allCenX).",<br />".array_sd($allCenY);
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
    <li>Comma-separated values denote a column vector (X and Y components).</li>
  </ol>
</div>
