<?php
if (empty($_GET['id'])) {
  die("No data.");
}

require '../../../../config.php';
$hn = new Hypernote($_GET['id'], $_GET['login'], $_GET['time']);
$notes = $hn->getData();
// when login and time are both passed, there's only one hypernote
if ($notes) {
  echo utf8_decode($notes[0]['txt']); // page has no encoding
} else {
  echo "It seems that ".$_GET['login']." did not leave a hypernote for this movie at that time.";
}
?>

<hr/>
<a href="#" onclick="history.go(-1);return false">Back</a> 
