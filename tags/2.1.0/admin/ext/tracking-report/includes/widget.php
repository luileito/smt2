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
    d.style.zIndex = level+10;
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
    d.style.fontWeight = "bold";
    d.style.color = "#FFF";
    d.style.backgroundColor = bgColor;

    // surround element
    //if (elm) elm.style.border = "1px solid transparent";
    /*
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

function allocate_color($domlist, $palette)
{
  $c = array();
  $i = 0;
  foreach($domlist as $dom => $freq) {
    $index = (int)$freq;
    if (!array_key_exists($index, $c)){
      $c[$index] = $palette[$i];
      ++$i;
    }
  }

  return $c;
}

if ($hovered) {
  $palette = array("00F", "00D", "00C", "00B", "009", "007", "005", "003", "001");
  $colors = allocate_color($hovered, $palette);
  foreach($hovered as $dom => $freq) {
    $cdata_widget .= ' displayFreq("'.$dom.'", "'.$freq.'", "#'.$colors[(int)$freq].'"); ' . PHP_EOL;
  }
}

if ($clicked) {
  $palette = array("F00", "D00", "C00", "B00", "900", "700", "500", "300", "100");
  $colors = allocate_color($clicked, $palette);
  foreach($clicked as $dom => $freq) {
    $cdata_widget .= ' displayFreq("'.$dom.'", "'.$freq.'", "#'.$colors[(int)$freq].'"); ' . PHP_EOL;
  }
}
$cdata_widget .= '
  });
  
})();
//]]>
';
// create user data script
$js_widget = $doc->createInlineScript($cdata_widget);

?>
