<?php
require dirname(__FILE__).'/class.trail.php';

if (isset($id)) 
{
  $ut = new UserTrail($clientId);
  $visits = $ut->getData();
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

// user object for tracking data
$cdata_user = '
//<![CDATA[
var _smtViewportSize = window.smtAuxFn.getWindowSize();
// (smt) user data object
var smtData = {
    wprev: '.$viewportWidth.',
    hprev: '.$viewportHeight.',
    wcurr: _smtViewportSize.width,
    hcurr: _smtViewportSize.height,
    xcoords: ['.$coordsX.'],
    ycoords: ['.$coordsY.'],
    xclicks: ['.$clicksX.'],
    yclicks: ['.$clicksY.'],
    xclusters: ['.implode(",", $clusterX).'],
    yclusters: ['.implode(",", $clusterY).'],
    clustsize: ['.implode(",", $clusterSize).'],
    trails: ['.$trails.'],
    currtrail: '.$currTrailId.',
    trailurl: "'.TRACKER.'",
    fps: '.$fps.'
};
//]]>
';
// create user data script
$js_user = createInlineScript($doc, $cdata_user);
?>