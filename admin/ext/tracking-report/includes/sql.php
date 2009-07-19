<?php
$sql = db_select(TBL_PREFIX.TBL_RECORDS, "*", "id='".$id."'");
if (!$sql) { die('No tracking data matching id #'.$id); }

$clientId       = $sql['client_id'];
$cacheId        = $sql['cache_id'];
$osId           = $sql['os_id'];
$browserId      = $sql['browser_id'];
$browserVer     = $sql['browser_id'];
$userAgent      = $sql['user_agent'];
$firstTimeUser  = $sql['ftu'];
$screenWidth    = (int) $sql['scr_width'];
$screenHeight   = (int) $sql['scr_height'];
$viewportWidth  = (int) $sql['vp_width'];
$viewportHeight = (int) $sql['vp_height'];
$date           = $sql['sess_date'];
$trackingTime   = (float) $sql['sess_time'];
$fps            = (int) $sql['fps'];
$coordsX        = $sql['coords_x'];
$coordsY        = $sql['coords_y'];
$clicksX        = $sql['clicks_x'];
$clicksY        = $sql['clicks_y'];
$hovered        = $sql['hovered'];
$clicked        = $sql['clicked'];

$html = db_select(TBL_PREFIX.TBL_CACHE, "url,file", "id='".$cacheId."'");
$htmlFile = $html['file'];
$url = $html['url'];
?>