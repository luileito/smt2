<?php
// check data first (exclude the root user)
if (empty($_POST) || $_COOKIE['smt-root']) exit;
require '../config.php';

$URL = $_POST['url'];

// get remote webpage
$request = get_remote_webpage(
                                $URL,
                                array( CURLOPT_COOKIE => $_POST['cookies'] )
                             );

$webpage = $request['content'];
// check request status
if ($request['errnum'] != CURLE_OK || $request['http_code'] != 200)
{
  $webpage = error_webpage('<h1>Could not fetch page</h1><pre>'.print_r($request, true).'</pre>');
  $parse = true;
} 
else 
{
  $cachedays = db_option(TBL_PREFIX.TBL_CMS, "cacheDays");
  // is cache enabled?
  if ($cachedays > 0) 
  {
    // get the most recent version saved of this page
    $cachelog = db_select(TBL_PREFIX.TBL_CACHE, "id,UNIX_TIMESTAMP(saved) as savetime", "url='".$URL."' ORDER BY id DESC");
    // check if url exists on cache, and if it should be stored (again) on cache
    if ($cachelog && (time() - $cachelog['savetime'] < $cachedays * 86400)) {
      // get HTML log id
      $logid = $cachelog['id'];
      $parse = false;
    } else {
      // cache days expired
      $parse = true;
    }
  } else {
    // cache is disabled
    $parse = true;
  }
  
  /* parse webpage ---------------------------------------------------------- */
  if ($parse) 
  {
    // use the DOM to parse webpage contents
    $dom = new DOMDocument();
    $dom->formatOutput = true;
    $dom->preserveWhiteSpace = false;
    // hide warnings when parsing non valid (X)HTML pages
    @$dom->loadHTML($webpage);
    // by removing (smt)2 scripts, a fresh copy of the original HTML page is created
    $scripts = $dom->getElementsByTagName("script");
    $scriptsToRemove = array();
    // mark
    foreach ($scripts as $script) 
    {
      $src = $script->getAttribute("src");
      // use hardcoded strings instead of defined constants since file versions will change
      if (strstr($src, "smt-record") || strstr($src, "smt-aux")) {
        $scriptsToRemove[] = $script;
      }
    }
    // sweep
    foreach ($scriptsToRemove as $script) {
      $script->parentNode->removeChild($script);
    }
    // set HTML log name
    $date = date("Ymd-His");
    $ext = ".html";
    // "March 10th 2006 @ 15h 16m 08s" should create the log file "20060310-151608.html" 
    $htmlfile  = (!is_file(CACHE_DIR.$date.$ext)) ?
                  $date.$ext :
                  $date.'-'.mt_rand().$ext; // random seed to avoid duplicated files
    // store (UTF-8 encoded) log
    $dom->saveHTMLFile( utf8_encode(CACHE_DIR.$htmlfile) );
    // insert new row on TBL_CACHE and look for inserted id
    $logid = db_insert(TBL_PREFIX.TBL_CACHE, "file, url, title, saved", "'".$htmlfile."', '".$URL."', '".$_POST['urltitle']."', NOW()");
  }
}

// verify
if (!isset($logid)) exit;

/* client browser stats ----------------------------------------------------- */

$browser = new Browser();

// save browser id
$bname = db_select(TBL_PREFIX.TBL_BROWSERS, "id", "name='".$browser->getBrowser()."'");
if (!$bname) {
  $browserid = db_insert(TBL_PREFIX.TBL_BROWSERS, "name", "'".$browser->getBrowser()."'");
} else {
  $browserid = $bname['id'];
}
// save OS id
$osname = db_select(TBL_PREFIX.TBL_OS, "id", "name='".$browser->getPlatform()."'");
if (!$osname) {
  $osid = db_insert(TBL_PREFIX.TBL_OS, "name", "'".$browser->getPlatform()."'");
} else {
  $osid = $osname['id'];
}

/* create database entry ---------------------------------------------------- */
$fields  = "client_id,cache_id,os_id,browser_id,browser_ver,user_agent,";
$fields .= "ftu,scr_width,scr_height,vp_width,vp_height,";
$fields .= "sess_date,sess_time,fps,coords_x,coords_y,clicks_x,clicks_y,hovered,clicked"; 

$values  = "'". get_client_id()                     ."',";
$values .= "'". $logid                              ."',";
$values .= "'". $osid                               ."',";
$values .= "'". $browserid                          ."',";
$values .= "'". (float) $browser->getVersion()      ."',";
$values .= "'". $browser->getUserAgent()            ."',";

$values .= "'". (int) $_POST['ftu']                 ."',";
$values .= "'". (int) $_POST['screenw']             ."',";
$values .= "'". (int) $_POST['screenh']             ."',";
$values .= "'". (int) $_POST['pagew']               ."',";
$values .= "'". (int) $_POST['pageh']               ."',";

$values .= "NOW(),";
$values .= "'". (float) $_POST['time']              ."',";
$values .= "'". (int)   $_POST['fps']               ."',";
$values .= "'". $_POST['xcoords']                   ."',";
$values .= "'". $_POST['ycoords']                   ."',";
$values .= "'". $_POST['xclicks']                   ."',";
$values .= "'". $_POST['yclicks']                   ."',";
$values .= "'". array_sanitize($_POST['elhovered']) ."',";
$values .= "'". array_sanitize($_POST['elclicked']) ."'";

$uid = db_insert(TBL_PREFIX.TBL_RECORDS, $fields, $values);
// send user ID back to the record script
echo $uid;
?>