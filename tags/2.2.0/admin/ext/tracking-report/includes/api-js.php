<?php
// create jsGraphics script
$js_graphics = $doc->createExternalScript(WZ_JSGRAPHICS);
// load JSON parser
$js_json = $doc->createExternalScript(JSON_PARSER);

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
smt2.replay({
  '.implode(",".PHP_EOL, $customprop).'
});
';

if (db_option(TBL_PREFIX.TBL_CMS, "refreshOnResize")) {
  $cdata_options .= '
    (function(){
      smt2fn.addEvent(window, "resize", smt2fn.reloadPage);
    })();
  ';
}

$cdata_options .= '
//]]>
';
// create user data script
$js_options = $doc->createInlineScript($cdata_options);
// create replay script (and insert it before $js_options)
$js_replay = $doc->createExternalScript(SMT_REPLAY);
?>