<h3>Client details</h3>

<table class="cms" cellpadding="10" cellspacing="1">
  <thead>
      <tr>
        <th>source URL</th>
        <th>cache log</th>
        <th>user agent</th>
        <th>resolution</th>
        <th>viewport</th>
        <th>tracking accuracy</th>
      </tr>
  </thead>
  <tbody>
  <?php
  $list  = '<tr class="odd">'.PHP_EOL;
  // log data  
  $list .= '<td>'.PHP_EOL;
  if ($log['url']) {
    $list .= '<a href="'.$log['url'].'" rel="external" title="'.$log['title'].'">'.trim_text($log['title']).'</a>';
  }
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .=  $log['file'].PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  // user agent data
  $list .= '<td>'.PHP_EOL;
  $list .= '<acronym title="'.$log['user_agent'].'">'.$log['browser'].' '.$log['browser_ver'].'</acronym> on '.$log['os'].PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  // screen & page data
  $list .= '<td>'.PHP_EOL;
  $list .= $log['scr_width'].' x '.$log['scr_height'].PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .= $log['vp_width'].' x '.$log['vp_height'].PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  // mouse tracking data
  $list .= '<td>'.PHP_EOL;
  $list .= $log['fps'].' fps'.PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  $list .= '</tr>'.PHP_EOL;
    
  echo $list;
  ?>
  </tbody>    
</table>