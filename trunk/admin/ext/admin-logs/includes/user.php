<?php
require dirname(__FILE__).'/class.trail.php';

if (isset($id)) 
{
  $ut = new UserTrail($clientId);
  $visits = $ut->getData();
  $trails = array();

  if (!count($visits)) die_msg("Log #$id not found");
  
  foreach ($visits as $v) {
    $trails[] = $v["id"]; 
  }
  // make it FlashVar friendly
  $trails = implode(",", $trails);
  $currTrailId = $id;
} else {
  // multiuser video
  $trails = null;
  $currTrailId = $pgid;
}

// videos can be linked to specific time
$deftime = "00:00";
$start = isset($_GET['start']) ? $_GET['start'] : $deftime;
$end   = isset($_GET['end'])   ? $_GET['end']   : $deftime;
// user object for tracking data
$cdata_user = '
//<![CDATA[
// (smt) user data object
var smt2data = {
  users: '    . json_encode('['.implode(",", $JSON).']') .',
  fps: '      . $fps               .',
  layout: "'  . $layoutType        .'",
  login: "'   . $_SESSION['login'] .'",
  trails: ['  . $trails            .'],
  currtrail: '. $currTrailId       .',
  start: "'   . $start             .'",
  end: "'     . $end               .'"
};
//]]>
';
// create user data script
$js_user = $doc->createInlineScript($cdata_user);
?>
