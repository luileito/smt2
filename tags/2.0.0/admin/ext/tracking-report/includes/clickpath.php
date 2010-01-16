<div id="trail">
  
  <h1 class="heading center">Click Path</h1>
  
  <?php
    require dirname(__FILE__).'/class.trail.php';
    $trail = new UserTrail($clientId);
    $visit = $trail->getNumTrails();
    
    if ($visit > 0) 
    {
      echo '<p class="center"><em>Hover on each link to see more details</em></p>';
      // build the list
      $list  = '<ol id="user-trail">'.PHP_EOL;
      foreach ($trail->getData() as $i => $data) 
      {
        // this ordered list is displayed inline, so insert index number manually 
        $list .= ' <li>';
        // add a nice arrow to point to the next log
        if ($i > 0) {
          $list .= '&rarr; '; 
        }
        // begin box
        $list .= '[ <strong>'. ++$i.'</strong>: '.PHP_EOL;
        // hover title
        $title = $data["date"].' # '.$data["time"].'sec';
        // some cache logs could be deleted... 
        if ($data["url"]) {
          $title .= ' @ '.$data["url"];
        }
        // check if we are browsing the current log
        $list .= ($data["id"] == $id) ? 
                  '<em title="'.$title.'">current (#'.$id.')</em>' :
                  '<a href="analyze.php?id='.$data["id"].'" title="'.$title.'">analyze</a>';
        // add a link to mouse replay
        $list .= ' | <a href="track.php?id='.$data["id"].'&amp;api=swf" title="'.$title.'">view</a>';
        if (is_root()) {
          $list .= ' | <a href="delete.php?id='.$data["id"].'" class="conf">delete</a>';
        }
        // end box
        $list .= ' ]';
        // and list
        $list .= ' </li>'.PHP_EOL;
      }
      $list .= '</ol>';
      
      echo $list;
      
    } else { echo '<p class="center">There are no more browsed pages for this user.</p>'; }
  ?>
  
</div><!-- end trail -->