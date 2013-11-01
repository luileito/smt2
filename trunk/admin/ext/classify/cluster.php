<?php
$rows = db_select_all(TBL_PREFIX.TBL_RECORDS, "*", "domain_id='".$_POST['domain_id']."' ORDER BY id DESC");

if (count($rows) > 0) 
{
  $users = array();
  $pages = array();
  $dict_user = array();
  $dict_page = array();
  foreach ($rows as $row) {    
    // extract mouse features
    $mf = new MouseFeat(
        array(
          'x' => $row['coords_x'], 
          'y' => $row['coords_y'], 
          'c' => $row['clicks'], 
          'f' => $row['fps'], 
          'w' => $row['vp_width'], 
          'h' => $row['vp_height']
        )
    );
    // and use (some of) those features to cluster user behaviors (more features lead to slower computation!)
    $users[] = array(
      $mf->time, $mf->numClicks, $mf->activity, $mf->distance['x'], $mf->scrollReach['y']
    );
    // those behaviors may belong to different pages
    $cache = db_select(TBL_PREFIX.TBL_CACHE, "url", "id = '".$row['cache_id']."'");
    $url = $cache['url'];
    // check whether URLs should be merged (just remove query string)
    if (db_option(TBL_PREFIX.TBL_CMS, "mergeCacheUrl")) {
      $urlparts = explode("?", $url);
      $url = $urlparts[0];
    }
    if (isset($pages[$url])) {
      $pages[$url] += 1;
    } else {
      $pages[$url] = 1;
    }
    $dict_user[] = $row['id'];
    $dict_page[] = $url;
  }
  
  $n = count($pages);
  $k = ceil(sqrt($n/2));
  $km = new KMeans(whiten($users), $k);
  //$km->initRandom();  
  $km->initKatsavounidis();
  $km->doCluster();
  $groups = array();
  foreach ($km->clusters as $j => $cluster) {
    //echo '['.$j.']<br>';
    $groups[$j] = array();
    foreach ($cluster as $id => $feats) {
      //echo $id.': '.$dict_page[$id].', '.$dict_user[$id].'<br>';
      $url = $dict_page[$id];
      if (!isset($groups[$j][$url])) {
        $groups[$j][$url] = 1;
      } else {
        $groups[$j][$url] += 1;
      }
    }
  }

  $list  = '<p>The system has identified '.$k.' groups ('.count($users).' users browsed '.$n.' pages overall).<br/>';
  $list .= 'Looking at the following groups can give you an overview of what pages are clubbing similar behaviors.</p>';
  foreach ($groups as $i => $group) {
    $list .= '<div class="fl">';
    $list .= '<h3 class="mt">Group #'.$i.'</h3>';
    $list .= '<ul class="mr indent">';
    foreach ($group as $url => $count) {
      $list .= '<li><a href="'.$url.'" class="external">'.$url.'</a></li>';
    }
    $list .= '</ul>';
    $list .= '</div>';
  }
  $list .= '<br class="clear"/>';
  $list .= '<p class="mt">Sometimes the same page can be assigned to more than one group, ';
  $list .= 'since all pages were classified according to user interactions.</p>';  
  echo $list; 
} 
else 
{
  echo 'No records were found for that domain.';
}
?>
