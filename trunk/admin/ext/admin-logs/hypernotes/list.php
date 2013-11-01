<?php
if (empty($_GET['id'])) {
  die("No data.");
}

require '../../../../config.php';
require SYS_DIR.'logincheck.php';

include 'body-ini.php';

$id = intval($_GET['id']);
$hn = new Hypernote($id, $_SESSION['login']);
$notes = $hn->getData();
if ($notes) {
  $list  = '<table class="cms" cellpadding="0" cellspacing="1">';
  $list .= '<thead>';
  $list .=  '<tr>';
  $list .=   '<th>user</th>';
  $list .=   '<th>time</th>';
  $list .=   '<th>content</th>';
  $list .=   '<th>action</th>';
  $list .=  '</tr>';  
  $list .= '</thead>';
  $list .= '<tbody>';
  foreach ($notes as $note) {
    $user = db_select(TBL_PREFIX.TBL_USERS, "login", "id='".$note['uid']."'");  
    // build row
    $list .= '<tr>';
    $list .=  '<td>'.$user['login'].'</td>';
    $qs = array(
                 'id'    => $id,
                 'start' => $note['pos']
               );
    $list .=  '<td><a href="../track.php?'.http_build_query($qs).'" class="track">'.$note['pos'].'</a></td>';
    $list .=  '<td>'.trim_text(strip_tags($note['txt']), 10).'</td>';
    $list .=  '<td>';
    $qs = array(
                 'id'    => $id,
                 'login' => $user['login'],
                 'time'  => $note['pos']
               );     
    $list .=  '<a href="read.php?' . http_build_query($qs) . '">read</a>';
    if ($_SESSION['login'] == $user['login'] || is_admin()) {
      $list .=  ' | <a href="edit.php?' . http_build_query($qs) . '">edit</a>';
    }
    if (is_root()) {
      $list .=  ' | <a class="conf" href="delete.php?' . http_build_query($qs) . '">delete</a>';
    }
        
    $list .=  '</td>';    
    $list .= '</tr>';    
  }
  $list .= '</tbody>';  
  $list .= '</table>';
  echo $list;
} else {
  echo "No hypernotes were found for this movie.";
}
?>

<script type="text/javascript">
$(function() {
  $('a.track').click( function(e) {
    e.preventDefault();
    self.opener.window.location.href = $(this).attr("href");
  });
});
</script>

<?php
//include 'body-end.php';
include INC_DIR.'footer.php';
?>
