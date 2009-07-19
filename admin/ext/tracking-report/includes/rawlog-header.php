<table cellpadding="10" cellspacing="1">
  <thead>
      <tr>
        <th>source URL</th>
        <th>cache log</th>
        <th>user agent</th>
        <th>screen</th>
        <th>tracking time</th>
      </tr>
  </thead>
  <tbody>
  <?php
  $list  = '<tr class="odd">'.PHP_EOL;
  // log data  
  $list .= '<td>'.PHP_EOL;
  $list .= '<a href="'.$sql['url'].'" rel="external" title="'.$sql['title'].'">'.trim_text($sql['title']).'</a>';
  $list .= '</td>'.PHP_EOL;
  $list .= '<td>'.PHP_EOL;
  $list .=  $sql['file'].PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  // user agent data
  $list .= '<td>'.PHP_EOL;
  $list .= '<acronym title="'.$sql['user_agent'].'">'.$sql['browser'].' '.$sql['browser_ver'].'</acronym> on '.$sql['os'].PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  // screen & page data
  $list .= '<td>'.PHP_EOL;
  $list .=  'resolution: '.$sql['scr_width'].'x'.$sql['scr_height'].'<br />';
  $list .= 'viewport: '.$sql['vp_width'].'x'.$sql['vp_height'].PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  // mouse tracking data
  $list .= '<td>'.PHP_EOL;
  $list .= $sql['sess_time'].' seg @ '.$sql['fps'].' fps'.PHP_EOL;
  $list .= '</td>'.PHP_EOL;
  $list .= '</tr>'.PHP_EOL;
    
  echo $list;
  ?>
  </tbody>    
</table>