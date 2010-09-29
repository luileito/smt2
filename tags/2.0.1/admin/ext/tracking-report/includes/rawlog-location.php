<?php
// connect to geobytes web service
$ws = "http://www.geobytes.com/IpLocator.htm?GetLocation&template=xml.txt&ipaddress=".base64_decode($clientId);

$xml = get_remote_webpage($ws);
$doc = simplexml_load_string($xml['content']);
$lat = (float) $doc->latitude;
$lng = (float) $doc->longitude;
?>

<div id="map" class="center"></div>
<script type="text/javascript" src="http://www.google.com/jsapi?key=<?=GG_KEY?>"></script>
<script type="text/javascript">
// <![CDATA[
google.load("maps", "2");
function initialize() {
  var map = new google.maps.Map2(document.getElementById("map"));
  var point = new google.maps.LatLng(<?=$lat?>, <?=$lng?>);
  map.setCenter(point, 6);
  var marker = new google.maps.Marker(point);
  map.addOverlay(marker);
  map.addControl(new GSmallMapControl());
}
google.setOnLoadCallback(initialize);
// ]]>
</script>