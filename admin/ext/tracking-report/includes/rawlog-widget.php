<?php
function computeDOMelements($arr, $name)
{
  $widget = array_frequency($arr, 5);
  if (!$widget) {
    $status = 'There were no '.$name.'ed elements';
  } else {
    $status = "";
    foreach ($widget as $elem => $freq){
      $status .= $elem.' = '.$freq.'%<br />';
    }
  }
  return $status;
}

if (isset($_GET['cid'])) { 
  echo '<p class="center"><em>This table is computed for ALL pages that this user browsed. Thus, here you have their user model.</em></p>'; 
}
else if (isset($_GET['pid'])) { 
  echo '<p class="center"><em>This table takes into account ALL users that browsed 
        <a rel="external" href="track.php?pid='.$_GET['pid'].'&amp;api=swf">this page</a>.
        Thus, here you have the page model.</em></p>'; 
}
?>

<h3 class="mt">Interacted elements</h3>

<table class="cms" cellpadding="10" cellspacing="1">
  <thead>
      <tr>
        <th>hovered elements (frequency)</th>
        <th>clicked elements (frequency)</th>
      </tr>
  </thead>
  <tbody>
  <?php
  $list  = '<tr class="odd">'.PHP_EOL;
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