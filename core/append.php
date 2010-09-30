<?php
// check data first
if (empty($_POST)) exit;
require_once '../config.php';

$values  = "sess_time = '".                         (float) $_POST['time']    ."',";
$values .= "vp_width  = '".                         (int)   $_POST['pagew']   ."',";
$values .= "vp_height = '".                         (int)   $_POST['pageh']   ."',";
$values .= "coords_x  = CONCAT(COALESCE(coords_x, ''), ',". $_POST['xcoords'] ."'),";
$values .= "coords_y  = CONCAT(COALESCE(coords_y, ''), ',". $_POST['ycoords'] ."'),";
$values .= "clicks_x  = CONCAT(COALESCE(clicks_x, ''), ',". $_POST['xclicks'] ."'),";
$values .= "clicks_y  = CONCAT(COALESCE(clicks_y, ''), ',". $_POST['yclicks'] ."'),";
$values .= "hovered   = CONCAT(COALESCE(hovered,  ''), ',". array_sanitize($_POST['elhovered']) ."'),";
$values .= "clicked   = CONCAT(COALESCE(clicked,  ''), ',". array_sanitize($_POST['elclicked']) ."')";

db_update(TBL_PREFIX.TBL_RECORDS, $values, "id='".$_POST['uid']."'");
?>