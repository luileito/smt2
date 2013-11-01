<?php 
// a BASE element is needed to link correctly CSS, scripts, etc.
$base = $doc->createElement('base');
$base->setAttribute('href', url_get_base($url));

$ini_comm = $doc->createComment(" begin (smt)2 tracking code ");
$end_comm = $doc->createComment(" end (smt)2 tracking code ");
$api_comm = $doc->createComment(" load (smt)2 drawing API ");

// point to (smt) aux functions
$js_aux = $doc->createExternalScript(SMT_AUX);
// and peppy selector library
$js_selector = $doc->createExternalScript(JS_SELECTOR);

// rebuild parsed page
$head = $doc->getElementsByTagName('head');
foreach ($head as $h) {
  // loading order is crucial!
  $h->insertBefore($base, $h->firstChild);
  $h->appendChild($ini_comm);
  $h->appendChild($js_aux);
  $h->appendChild($js_user);
  $h->appendChild($js_selector);
  if (isset($js_widget)) $h->appendChild($js_widget);
  $h->appendChild($api_comm);
  
  if ($api == "js") {
    $h->appendChild($js_graphics);
    $h->appendChild($js_json);
  } else if ($api == "swf") {
    $h->appendChild($swfobject);
    $h->appendChild($css_swf);
  }
  $h->appendChild($end_comm);
}
// append tracking script at the end of the page body
$body = $doc->getElementsByTagName('body');
foreach ($body as $b) {
  if ($api == "js") {
    $b->appendChild($js_replay);
    $b->appendChild($js_options);
  } else if ($api == "swf") {
    $b->appendChild($js_swf);
  }
}
?>