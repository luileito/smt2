<?php
// load base functions
define('REL_URL', "../../../");
require_once REL_URL.'config.php';

// get prioritized sections
$prioritized = get_exts_order();
// and availabe sections too
$exts = ext_available();
foreach ($prioritized as $dirname => $priority) 
{
  // skip deleted sections (do not remove from DB)
  if (!in_array($dirname, $exts)) { continue; }
  
  $div .= '<div id="'.$dirname.'" class="groupItem">';
  $div .= ' <div class="itemHeader">'.filename_to_str($dirname).'</div>';
  $div .= '</div>';
}

echo $div;
?>