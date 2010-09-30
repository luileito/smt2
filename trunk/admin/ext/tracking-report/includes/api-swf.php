<?php
// create SWFObject script
$swfobject = $doc->createExternalScript(SWFOBJECT);
// (smt) tracking layer identifier
$smtID = "smtTrackingLayer";
// write Flash object in (smt) tracking layer
$cdata_swf = '
//<![CDATA[
(function(){

    var att = { scaleMode: "noscale" },                         // SWF attributes
        par = { allowFullScreen: true, wmode: "transparent" },  // SWF parameters
        aux = window.smt2fn,                                    // (smt) aux functions
        dat = window.smt2data;                                  // (smt) user data

    aux.addEvent(window, "load", function(){
    //aux.onDOMload(function(){
      // replay mouse track over Flash objects 
      aux.allowTrackingOnFlashObjects();
      // compute the page size
      var doc = aux.getPageSize();
      dat.hpage = doc.height;
      dat.wpage = doc.width;
      // compute viewport size
      var win = aux.getWindowSize();
      dat.wcurr = win.width;
      dat.hcurr = win.height;
      // avoid IE bug (ActiveX player): use computed page size instead of the ones reported by SWF stage
      swfobject.embedSWF("'.SWF_PATH.'tracking.swf", "'.$smtID.'", doc.width, doc.height, "10.0.0", "'.SWF_PATH.'expressInstall.swf", dat, par, att);
      // render Tracking layer on top
      var smtId = document.getElementById("'.$smtID.'");
      smtId.style.zIndex = aux.getNextHighestDepth() + 1;
    });
';

if (db_option(TBL_PREFIX.TBL_CMS, "refreshOnResize")) {
  $cdata_swf .= '
      aux.addEvent(window, "resize", aux.reloadPage);
  ';
}

$cdata_swf .= '
})();
//]]>
';

// create user data script
$js_swf = $doc->createInlineScript($cdata_swf);
// apply styles to (smt) tracking layer (huge font size to warn non-javascript browsers)
$css_swf = $doc->createInlineStyleSheet("#".$smtID." { margin:0; padding:0; position:absolute; top:0; left:0; overflow:hidden; outline:none; font-size:200%; }");

// create (smt) layer
$smtDiv = $doc->createDiv($smtID, "Loading smt2 canvas tracking layer...");
$body = $doc->getElementsByTagName('body');
foreach ($body as $b) { 
  $b->appendChild($smtDiv);
}
?>