<?php
// get interacted elements
$hovered = array_frequency($hovered);
$clicked = array_frequency($clicked);

$cdata_widget = '
//<![CDATA[
(function(){
  var aux       = window.smt2fn,
      level     = aux.getNextHighestDepth(),
      displayed = [];
      sizes     = [];
  
  function createDiv(content)
  {
    var d = document.createElement("div");
    var text = document.createTextNode(content);
    d.appendChild(text);

    document.body.appendChild(d);

    return d;
  }

  function displayFreq(dom, freq, bgColor)
  {
    var elm = Selector(dom)[0];
    var pos = aux.findPos(elm);
    
    var d = createDiv(freq + "%");
    d.style.position = "absolute";
    d.style.zIndex = level;
    d.style.top = pos.y + "px";
    var xpos = pos.x;
    /*
    var i = aux.array.indexOf(displayed, pos.x);
    if (i != -1) {
      xpos = displayed[i] + sizes[i];
    }
    */
    xpos += "px";
    d.style.left = xpos;
    
    d.style.margin = 0;
    d.style.padding = "1px";
    d.style.fontSize = "10px";
    d.style.color = "#FFF";
    d.style.backgroundColor = bgColor;
    /*
    // surround element
    if (elm) elm.style.border = "1px solid " + bgColor;
    // save positions to prevent overlapping
    displayed.push(pos.x);
    try {
      var w = elm.offsetWidth;
      sizes.push(w);
    } catch(err) {} // elm could be undefined
    */
  }
  
  aux.addEvent(window, "load", function(){
';

if ($hovered) foreach($hovered as $dom => $freq)
{
  $cdata_widget .= ' displayFreq("'.$dom.'", "'.$freq.'", "#000"); ' . PHP_EOL;
}

if ($clicked) foreach($clicked as $dom => $freq)
{
  $cdata_widget .= ' displayFreq("'.$dom.'", "'.$freq.'", "#F00"); ' . PHP_EOL;
}

$cdata_widget .= '
  });
  
})();
//]]>
';
// create user data script
$js_widget = $doc->createInlineScript($cdata_widget);

?>