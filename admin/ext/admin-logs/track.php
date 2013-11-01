<?php
// server settings are required - relative path to smt2 root dir
require '../../../config.php';
// protect extension from being browsed by anyone
require SYS_DIR.'logincheck.php';

include './includes/settings.php';  // custom tracking analysis options
include './includes/sql.php';       // load tracking data from database

// A fallback error page:
$errpage  = '<h1>Page not found on cache!</h1>';
$errpage .= '<p>Error loading file <code>'.$htmlFile.'</code>, which is a snapshot of <code>'.$url.'</code></p>';
$errpage .= '<p>Some reasons for this issue include the following:</p>';
$errpage .= '<ol>';  
$errpage .=  '<li>Cache request could not be processed at the time.</li>';
$errpage .=  '<li>The cache log was deleted.</li>';
$errpage .=  '<li>The cache dir has been moved/renamed.</li>';  
$errpage .= '</ol>';
$errpage .= '<p>As a fallback solution, mark the option <code>fetchOldUrl</code> in the <em>Customize</em> section and reload this page.</p>'; 
  
// parse HTML log
$file = CACHE_DIR.$htmlFile;
$doc = new DOMUtil();
if (db_option(TBL_PREFIX.TBL_CMS, "fetchOldUrl")) {
  // try to re-fetch page, if available
  $request = get_remote_webpage($url);  
  $page = $request ? $request['content'] : error_webpage();
  // hide warnings when parsing non valid (X)HTML pages
  @$doc->loadHTML($page);
  remove_smt_scripts($doc);
} else if (!is_file($file)) {  
  // page not in cache and not fetched
  @$doc->loadHTML(error_webpage($errpage));
  remove_smt_scripts($doc);
} else {
  // page in cache (smt scripts were already removed)
  @$doc->loadHTMLFile( utf8_decode($file) );
}

// include user data
include './includes/user.php';
// hilite hovered/clicked elements
if (db_option(TBL_PREFIX.TBL_CMS, "displayWidgetInfo")) {
  include './includes/widget.php';
}
// include drawing API
$api = "swf"; //$_GET['api'];
$apiFile = './includes/api-'.$api.'.php';
if (!file_exists($apiFile)) { die("API file not found!"); }
include $apiFile;
include './includes/api-parse.php';
// finally render page
echo $doc->saveHTML();
?>
