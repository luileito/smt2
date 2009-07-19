<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';
// now you have access to all (smt) API functions and constants

// get log identifier
$id  = (int) $_GET['id'];
// use JS or SWF drawing API
$api = $_GET['api'];
// check API file before including more options
$apiFile = './includes/api-'.$api.'.php';
if (!file_exists($apiFile)) { die("API file not found!"); }

include './includes/settings.php';  // custom tracking analysis options
include './includes/sql.php';       // load tracking data from database
include './includes/kmeans.php';    // compute k-means clustering

// parse HTML log
$file = CACHE.$htmlFile;
// check
if (!is_file($file)) { die("Log not found on cache!"); }
// load file
$doc = new DOMDocument();
$doc->preserveWhitespace = false;
$doc->formatOutput = true;
// hide warnings when parsing non valid (X)HTML pages
@$doc->loadHTMLFile($file);
// use this constant to load more user trails, if available
define (TRACKER, getThisURLAddress());
// include user data
include './includes/user.php';
// include drawing API: SWF or JS
include $apiFile;
include './includes/api-parse.php';
// save the parsed page
$page = $doc->saveHTML();
// and render it
echo $page;
?>