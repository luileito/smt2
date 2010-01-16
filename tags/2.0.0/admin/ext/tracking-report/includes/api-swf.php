<?php
// create SWFObject script
$swfobject = createExternalScript($doc, SWFOBJECT);
// (smt) tracking layer identifier
$smtID = "smtTrackingLayer";
// write Flash object in (smt) tracking layer
$cdata_swf = '
//<![CDATA[
(function(){

    var att = { scaleMode: "noscale" },  // SWF attributes
        par = { wmode: "transparent" },  // SWF parameters
        aux = window.smtAuxFn,           // (smt) aux functions
        dat = window.smtData;            // (smt) user data

    aux.onDOMload(function(){
      // replay mouse track over Flash objects 
      aux.allowTrackingOnFlashObjects();
      // compute the page size
      var doc = aux.getPageSize();
      // avoid IE bug (ActiveX player): use these values instead of the SWF stage ones
      dat.hview = doc.height;
      dat.wview = doc.width;   
      swfobject.embedSWF("'.SWF_PATH.'tracking.swf?'.time().'", "'.$smtID.'", doc.width, doc.height, "9.0.0", "'.SWF_PATH.'expressInstall.swf", dat, par, att);
      
      // render the Tracking layer on top
      var smtId = document.getElementById("'.$smtID.'");
      smtId.style.zIndex = aux.getNextHighestDepth() + 1;
    });
    
    //aux.addEvent(window, "resize", aux.reloadPage);
    
})();
//]]>
';

// create user data script
$js_swf = createInlineScript($doc, $cdata_swf);
// apply styles to (smt) tracking layer (huuge font size to warm non-javascript browsers)
$css_swf = createInlineStyleSheet($doc, "#".$smtID." { margin:0; padding:0; position:absolute; top:0; left:0; overflow:hidden; outline:none; font-size:500%; }");

// create (smt) layer
$smtDiv = createDiv($doc, $smtID, "(smt) canvas layer. Enable JavaScript and Flash to replay this log!");
$body = $doc->getElementsByTagName('body');
foreach ($body as $b) { 
  $b->appendChild($smtDiv);
}
?>