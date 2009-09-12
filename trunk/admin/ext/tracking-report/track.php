<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require INC_PATH.'sys/logincheck.php';

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
// load file
$doc = new DOMDocument();
$doc->preserveWhitespace = false;
$doc->formatOutput = true;
// check
if (!is_file($file)) { 
  $file = error_webpage('<h1>Page not found on cache!</h1><p>That\'s because it was deleted from cache.</p>');
  // hide warnings when parsing non valid (X)HTML pages
  @$doc->loadHTML($file);
} else {
  @$doc->loadHTMLFile($file);
}
// use this constant to load more user trails, if available
define (TRACKER, $_SERVER['PHP_SELF']); //getThisURLAddress()
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