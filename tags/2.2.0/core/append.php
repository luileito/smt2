<?php
// check data first (exclude registered users)
if (empty($_POST) || isset($_COOKIE['smt-usr'])) die(":(");

require_once '../config.php';

$values  = "sess_time = '". (float) $_POST['time']   ."',";
$values .= "vp_width  = '". (int)   $_POST['pagew']  ."',";
$values .= "vp_height = '". (int)   $_POST['pageh']  ."',";
$values .= "coords_x  = CONCAT(COALESCE(coords_x, ''), ',". $_POST['xcoords'] ."'),";
$values .= "coords_y  = CONCAT(COALESCE(coords_y, ''), ',". $_POST['ycoords'] ."'),";
$values .= "clicks    = CONCAT(COALESCE(clicks,   ''), ',". $_POST['clicks']  ."'),";
$values .= "hovered   = CONCAT(COALESCE(hovered,  ''), ',". array_sanitize($_POST['elhovered']) ."'),";
$values .= "clicked   = CONCAT(COALESCE(clicked,  ''), ',". array_sanitize($_POST['elclicked']) ."')";

db_update(TBL_PREFIX.TBL_RECORDS, $values, "id='".$_POST['uid']."'");
?>
