<div id="trail">
  
  <h1 class="heading center">Click Path</h1>
  
  <?php
    require dirname(__FILE__).'/class.trail.php';
    $trail = new UserTrail($clientId);
    $visit = $trail->getNumTrails();
    
    if ($visit > 0) 
    {
      // show this text if there are more visited pages
      if ($visit > 1) { echo '<p class="center">This user also visited these pages:</p>'; }
      // anyway, build a list
      $list  = '<ol id="user-trail">'.PHP_EOL;
      foreach ($trail->getData() as $i => $data) 
      {
        // this ordered list is displayed inline, so insert index number manually 
        $list .= ' <li>'.++$i.'. '.PHP_EOL;
        $title = $data["date"].' # '.$data["url"];
        // check if we are browsing the current log
        $list .= ($data["id"] == $id) ? 
                  '<em title="'.$title.'">current (#'.$id.')</em>' :
                  '<a href="analyze.php?id='.$data["id"].'" title="'.$title.'">view</a>';
        $list .= ' </li>'.PHP_EOL;
      }
      $list .= '</ol>';
      
      echo $list;
      
    } else { echo '<p class="center">This user did not explore more web pages.</p>'; }
  ?>
  
</div><!-- end trail -->