<div id="trail">
  
  <h1 class="heading center">Click Path</h1>
  
  <?php
    /*
    $analyze_icon = CSS_PATH.'track-analyze.png';
    $delete_icon = CSS_PATH.'track-remove.png';
    $view_icon = CSS_PATH.'track-view.png';
    */
    require dirname(__FILE__).'/class.trail.php';
    $trail = new UserTrail($clientId);
    $visit = $trail->getNumTrails();
    
    if ($visit > 0)
    {
      echo '<p class="center"><em>Hover on each link to see more details</em>.</p>';
      // build the list
			$list  = '<div id="trail-wrap">'.PHP_EOL;
      $list .= '<ol class="user-trail">'.PHP_EOL;
			$count = 0;
			$prevData = null;
      foreach ($trail->getData() as $i => $data) 
      {
				if ($data["trail"] > $prevData["trail"]) {
					$list .= '</ol><ol class="user-trail">';
					$count = 0;
				}

        // this ordered list is displayed inline, so index numbers should be inserted explicitly 
        $list .= ' <li>';
        // add a nice arrow to point to the next log
        if ($count > 0) {
          $list .= '&rarr; '; 
        }
        // begin box
				$list .= '[ <strong>'.($count+1).'</strong> <span class="content">'.PHP_EOL;
        // hover title
        $title = $data["date"].' # '.$data["time"].'sec';
        // some cache logs could have been deleted... 
        if ($data["url"]) {
          $title .= ' @ '.$data["url"];
        }
        // check if we are browsing the current log
        $list .= (isset($id) && $data["id"] == $id) ? 
                  '<em class="current" title="'.$title.'">current (#'.$id.')</em>' :
                  '<a href="analyze.php?id='.$data["id"].'" class="analyze" title="'.$title.'">analyze</a>';
        // add a link to mouse replay
        $list .= ' | <a href="track.php?id='.$data["id"].'" class="view" rel="external" title="'.$title.'">view</a>';
        if (is_root()) {
          $list .= ' | <a href="delete.php?id='.$data["id"].'" class="del">delete</a>';
        }
        // end box ...
        $list .= '</span> ]';
        // ... and list
        $list .= ' </li>'.PHP_EOL;
				// update
				$prevData = $data;
				$count++;
      }
      $list .= '</ol>';
			$list .= '</div>';
      
      echo $list;
      
    } else { echo '<p class="center">There are no more browsed pages for this user.</p>'; }
  ?>
  
</div><!-- end trail -->

<script type="text/javascript">
//<![CDATA[
$(function(){
    // add some functionality...
    if ( $('#trail-wrap').length > 0 )
    {
      // create toggle links
      $('#trail p.center').append(
        ' <a href="#showtrails" class="showall">show all</a>' +
        ' <a href="#hidetrails" class="hideall">hide all</a>'
      );
      $('a.showall').click(function(e){
        $('.user-trail li strong').next().show();
      });
      $('a.hideall').click(function(e){
        $('.user-trail li strong').next().hide();
      });
      // enable toggling one set alone
      $('.user-trail li:first-child').prepend('<a href="#">&raquo;</a> ')
        .click(function(e){
          var lineItems = $(this).parent().children().find('strong').next();
          lineItems.toggle();
          e.preventDefault();
        });

      // hide trail options initially
      $('.user-trail li span.content').hide();
      // but show current
      try {
        $('.user-trail li').find('.current').parent().show();
      } catch(err){} // data is grouped

      // clicking on the trail number will toggle the info
      $('.user-trail li strong').css('cursor', "pointer").click(function(e){
        $(this).next().toggle(); 
      });
      
      // cancel toggle effect when clicking on links
      $('a.analyze, a.view, a.del').click(function(e){
        e.stopPropagation();
      });
    }
});
//]]>
</script>