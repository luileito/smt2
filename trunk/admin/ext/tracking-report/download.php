<?php
// server settings are required - relative path to smt2 root dir
require_once '../../../config.php';

// ensure that we can create ZIP files
if (!class_exists('ZipArchive')) die("Your PHP distribution does not support ZipArchive.");

if (isset($_SESSION['filterquery'])) $where = $_SESSION['filterquery'];
else if (isset($_POST))       $where = "1";
else if (isset($_GET['id']))  $where = "id='". $_GET['id'] ."'";
else if (isset($_GET['pid'])) $where = "id='". $_GET['pid'] ."'";
else if (isset($_GET['cid'])) $where = "id='". $_GET['cid'] ."'";
else $where = "1"; // default: download all logs

$records = db_select_all(TBL_PREFIX.TBL_RECORDS, "*", $where." ORDER BY sess_date, client_id");
if (!$records) { die("No logs found matching your criteria!"); }

$format = isset($_POST['format']) ? $_POST['format'] : "csv";

switch ($format) {
  case 'txt':
  case 'xml':  
    die("Sorry, TXT and XML formats are not yet implemented.");
    break;
  case 'csv':
  default:
    $delimiter = ";";
    break;
     
  case 'tsv':
    $delimiter = "\t";
    break;
}

// get column names first
$headers = db_records(true);
// parse DSV formats
$dsv = implode(",", $headers);
foreach ($records as $i => $r) {
  $row = array();
  foreach ($headers as $h) {
    // exclude interacted elements?
    if ($h != "hovered" && $h != "clicked") {
      $row[] = '"'.$r[$h].'"';
    }
  }
  $dsv .= PHP_EOL.implode($delimiter, $row);
}

// set ZIP name (in a writeable dir)
$zipname = 'smt2logs-'.date("Ymd").'.zip';
$zippath = CACHE_DIR.'/'.$zipname;
// create ZIP file
$zip = new ZipArchive;
$res = $zip->open($zippath, ZipArchive::OVERWRITE);
if (!$res) {
  die('Cannot open ZIP file.');
}
// add meta comments
$comm = "Downloaded on ".date('l jS \of F Y h:i:s A')
$zip->setArchiveComment($comm);
$readme  = $comm.PHP_EOL;
$readme .= "Each column's value is delimited either by a colon (CSV) or a tab (TSV).".PHP_EOL;
//$readme .= "These are their names:".PHP_EOL;
//$readme .= implode(PHP_EOL, $headers);
$zip->addFromString("README", $readme);
$zip->addFromString("logs.".$format, $dsv);
if (!$zip->close()) {
  die('Cannot close ZIP file.');
}
// now force download
header('Content-type: application/zip');
header("Content-Length: ".filesize($zippath)); 
header('Content-Disposition: attachment; filename="'.$zipname.'"');
readfile($zippath);
unlink($zippath);
?>
