<?php
// server settings are required - relative path to smt2 root dir
require_once '../../../config.php';

$where =  !empty($_SESSION['filterquery']) ? $_SESSION['filterquery'] : 
          !empty($_GET['id'])  ? "id='". $_GET['id'] ."'" : 
          !empty($_GET['pid']) ? "pid='".$_GET['pid']."'" :
          !empty($_GET['cid']) ? "cid='".$_GET['cid']."'" :
          !empty($_GET['ip'])  ? "ip='". $_GET['ip'] ."'" :
          "1";
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
    $row[] = '"'.$r[$h].'"';
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
  die('Cannot create ZIP file.');
}
// add meta comments
$zip->setArchiveComment("Downloaded on ".date('l jS \of F Y h:i:s A'));
$readme  = "Each column's value is delimited by a new line. These are their names:".PHP_EOL;
$readme .= implode(PHP_EOL, $headers);
$zip->addFromString("README", $readme);
$zip->addFromString("logs.".$format, $dsv);
if (!$zip->close()) {
  die('Cannot create ZIP file.');
}
// now force download
header('Content-type: application/zip');
header("Content-Length: ".filesize($zippath)); 
header('Content-Disposition: attachment; filename="'.$zipname.'"');
readfile($zippath);
unlink($zippath);
?>
