<?php
// shorcuts some globals
$coordsX = $sql['coords_x'];
$coordsY = $sql['coords_y'];
$clicksX = $sql['clicks_x'];
$clicksY = $sql['clicks_y'];
$hovered = $sql['hovered'];
$clicked = $sql['clicked'];

function computeDOMelements($arr, $name)
{
  $widget = compute_frequency($arr, 5);
  if (!$widget) {
    $status = 'This user did not '.$name.' any element';
  } else {
    foreach ($widget as $elem => $freq){
      $status .= $elem.' = '.$freq.'% of the time<br />';
    }
  }
  return $status;
}
?>

<table cellpadding="10" cellspacing="1">
  <thead>
      <tr>
        <th>hovered elements</th>
        <th>clicked elements</th>
      </tr>
  </thead>
  <tbody>
  <?php
  $list  = '<tr class="even">'.PHP_EOL;
  // log data  
  $list .= '<td>'.PHP_EOL;
  $list .= computeDOMelements($hovered, "hover");
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= computeDOMelements($clicked, "click");
  $list .= '</td>'.PHP_EOL;
  $list .= '</tr>'.PHP_EOL;
  
  echo $list;
  ?>
  </tbody>    
</table>