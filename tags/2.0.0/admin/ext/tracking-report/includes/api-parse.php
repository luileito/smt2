<?php 
// a BASE element is needed to link correctly CSS, scripts, etc.
$base = $doc->createElement('base');
$base->setAttribute('href', getBase($url)); // $_SERVER['SERVER_ADDR']

$ini_comm = $doc->createComment(" begin (smt)2 tracking code ");
$end_comm = $doc->createComment(" end (smt)2 tracking code ");
$api_comm = $doc->createComment(" load (smt)2 drawing API ");

// point to (smt) aux functions
$js_aux = createExternalScript($doc, SMT_AUX);

// rebuild parsed page
$head = $doc->getElementsByTagName('head');
foreach ($head as $h) {
  // loading order is crucial!
  $h->insertBefore($base, $h->firstChild);
  $h->appendChild($ini_comm);
  $h->appendChild($js_aux);
  $h->appendChild($js_user);
  $h->appendChild($api_comm);
  if ($api == "js") {
    $h->appendChild($js_graphics);
    $h->appendChild($js_options);
    $h->appendChild($js_replay);
  } else if ($api == "swf") {
    $h->appendChild($swfobject);
    $h->appendChild($js_swf);
    $h->appendChild($css_swf);
  }
  $h->appendChild($end_comm);
}
?>