<?php
// create ZIP file
$zip = new ZipArchive;
$res = $zip->open($zipname, ZipArchive::OVERWRITE);

if ($res !== true) {
	die('Error creating ZIP file for downloading.');
}

// create "readme" file
$readme  = "Each column's value is delimited by a new line. These are their names:".PHP_EOL;
$readme .= implode(PHP_EOL, $headers);
$zip->addFromString("README.txt", $readme);

// add meta comments
$zip->setArchiveComment("Downloaded on ".date('l jS \of F Y h:i:s A'));

// get log values
foreach ($records as $i => $r) 
{
	// create single TXT file
	foreach ($headers as $h) {
		$row[] = $r[$h];
	}
	// append file to ZIP
	$zip->addFromString($i.".".$format, implode(PHP_EOL, $row));
}
// end
if (!$zip->close()) {
	die('Cannot create ZIP file.');
}

// now force download
header('Content-type: application/zip');
header("Content-Length: ".filesize($zipname)); 
header('Content-Disposition: attachment; filename="'.$zipname.'"');
// output file
readfile($zipname);
unlink($zipname);
?>
