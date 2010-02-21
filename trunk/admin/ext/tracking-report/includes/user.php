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
  $trails = 0;
  $currTrailId = 0;
}

// there might be issues with IE and SWF here ($json must be escaped via JS)
$json = json_encode('['.implode(",", $JSON).']');

// user object for tracking data
$cdata_user = '
//<![CDATA[
// (smt) user data object
var smt2data = {
  fps: '.$fps.',
  users: escape('.$json.'),
  trails: ['.$trails.'],
  currtrail: '.$currTrailId.',
  trailurl: "'.TRACKER.'"
};
//]]>
';
// create user data script
$js_user = $doc->createInlineScript($cdata_user);
?>