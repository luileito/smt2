<?php
// create a "readme" file
$readme  = "Each column's value is delimited by a new line. These are their names:".PHP_EOL;
$readme .= implode(PHP_EOL, $headers);
$zip->addFromString("README", $readme);

$pad = strlen( count($records) );
// get log values
foreach ($records as $i => $r) 
{
	// create single TXT file
	foreach ($headers as $h) {
		$row[] = $r[$h];
	}
	// append file to ZIP
	$filename = sprintf('%0'.$pad.'d', ++$i) . ".".$format;
	$zip->addFromString($filename, implode(PHP_EOL, $row));
}
?>
