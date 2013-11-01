<?php
require '../../../../config.php';
// allow only the markup from SimpleTextEditor
$allow_tags = '<h1><h2><h3><p><pre><b><i><u><div><ul><ol><a><img>';
$data = strip_tags($_POST['data'], $allow_tags);

$hn = new Hypernote($_POST['id'], $_POST['login'], $_POST['time']);

if ($_POST['action'] == "reedit") 
{
  if ($hn->update($data)) {
    $msg  = '<p>';  
    $msg .= 'The hypernote was edited successfully.<br/>';
    //$msg .= 'You can close this window.';
    $msg .= '</p>';
  } else {
    $msg = "Cannot edit hypernote.";
  }
} 
else if ($_POST['action'] == "create") 
{
  if ($hn->insert($data)) {
    $msg  = '<p>';
    //$msg .= $_POST['login'] . ',<br/>';
    $msg .= 'The hypernote was created successfully, and it should be attached to the timeline.<br/>';
    //$msg .= 'You can close this window.';
    $msg .= '</p>';
    $msg .= '<hr/>';    
    $msg .= utf8_decode($data); // page has no encoding
  } else {
    $msg = "Cannot create hypernote.";
  }
} 
else 
{
  $msg = "Unknown editing action.";
}

echo $msg;
?>

<script type="text/javascript">
self.opener.document.getElementById("smtTrackingLayer").displayHyperNote("<?=$_POST['login']?>", "<?=$_POST['time']?>");
</script>   
