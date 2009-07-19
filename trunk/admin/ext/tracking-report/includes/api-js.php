<?php
// create jsGraphics script
$js_graphics = createExternalScript($doc, WZ_JSGRAPHICS);

// retrieve JS info from DB
$arrOptions = db_select_all(TBL_PREFIX.TBL_JSOPT, "*", "1");

// loop
foreach ($arrOptions as $o) 
{
  $prop = $o['name'];
  $val = $o['value'];

  if (empty($val) && $o['type'] == CMS_TYPE) { continue; }
  
  if ($o['type'] == CMS_TYPE) {
    // check 6 digits for colors
    while (strlen($val) < 6) {
		  $val .= "0";
		}
		// finally add the hex flag
    $val = '"#'.$val.'"';
  }
  // display JS object properties
  $customprop[] = "\t" . $prop . ': ' . $val;
}

$cdata_options = '
//<![CDATA[
var smtReplayOptions = {
'.implode(",".PHP_EOL, $customprop).'
};
//]]>
';
// create user data script
$js_options = createInlineScript($doc, $cdata_options);
// create (smt) replay script
$js_replay = createExternalScript($doc, SMT_REPLAY);
?>