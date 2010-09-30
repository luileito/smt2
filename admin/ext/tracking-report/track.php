<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';

// use JS or SWF drawing API
$api = $_GET['api'];
// check API file before including more options
$apiFile = './includes/api-'.$api.'.php';
if (!file_exists($apiFile)) { die("API file not found!"); }

include './includes/settings.php';  // custom tracking analysis options
include './includes/sql.php';       // load tracking data from database

// parse HTML log
$file = CACHE_DIR.$htmlFile;
// load file
$doc = new DOMUtil();
// check
if (!is_file($file)) { 
  $file = error_webpage('<h1>Page not found on cache!</h1><p>That\'s because either it was deleted from cache or the request could not be processed.</p>');
  // hide warnings when parsing non valid (X)HTML pages
  @$doc->loadHTML($file);
} else {
  // load (UTF-8 encoded) log
  @$doc->loadHTMLFile( utf8_decode($file) );
}
// use this constant to load more user trails, if available
define ('TRACKER', url_get_current());
// include user data
include './includes/user.php';

if (db_option(TBL_PREFIX.TBL_CMS, "displayWidgetInfo")) {
  // hilite hovered/clicked elements
  include './includes/widget.php';
}

// include drawing API: SWF or JS
include $apiFile;
include './includes/api-parse.php';
// save the parsed page
$page = $doc->saveHTML();
// and render it
echo $page;
?>