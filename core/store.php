<?php
// check data first (exclude the root user)
if (empty($_POST) || $_COOKIE['smt-root']) exit;
// use session data, if needed
session_start();

require '../config.php';

$URL = $_POST['url'];
// get remote webpage
$request = get_remote_webpage($URL);
$webpage = $request['content'];
// check request status
if ($request['errno'] != 0 || $request['http_code'] != 200) 
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
    // set encoding
    if (function_exists('mb_convert_encoding')) {
      $webpage = mb_convert_encoding($webpage, LOG_ENCODING, "auto"); // "auto" should detect the internal encoding
    } else {
      $trans = get_html_translation_table(HTML_ENTITIES);
      $webpage = strtr($webpage, $trans);
    }
    // use the DOM to parse webpage contents
    $dom = new DOMDocument();
    $dom->formatOutput = true;
    $dom->preserveWhiteSpace = false;
    // hide warnings when parsing non valid (X)HTML pages
    @$dom->loadHTML($webpage); 
    // add transfer info
    $info = " (smt)2 transfer info: ";
    foreach ($request as $key => $value) {
      if ($key === "content") { continue; }
      $info .= "[".$key."] => ".$value." \t";
    }
    // insert info at the end of page body 
    $debug = $dom->createComment($info);
    $body = $dom->getElementsByTagName("body");
    foreach ($body as $b) {
      $b->appendChild($debug);
    }
    // by removing (smt)2 scripts, a fresh copy of the original HTML page is created
    $scripts = $dom->getElementsByTagName("script");
    $scriptsToRemove = array();
    // mark
    foreach ($scripts as $script) 
    {
      $src = $script->getAttribute("src");
      // use hardcoded strings instead of defined constants since the files may change
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
    $type = ".html";
    // "March 10th 2006 @ 15h 16m 08s" should create the log file "20060310-151608.html" 
    $htmlfile  = (!is_file(CACHE.$date.$type)) ? 
                  $date.$type : 
                  $date.'-'.mt_rand().$type; // random seed to avoid duplicated files
    // store log
    $dom->saveHTMLFile(CACHE.$htmlfile);
    // insert new row on TBL_CACHE and look for inserted id
    $logid = db_insert(TBL_PREFIX.TBL_CACHE, "file, url, title, saved", "'".$htmlfile."', '".$URL."', '".$_POST['urltitle']."', NOW()");
  }
}

/* client browser stats ----------------------------------------------------- */

// save browser id
$browser = db_select(TBL_PREFIX.TBL_BROWSERS, "id", "name='".$_POST['bname']."'");
if (!$browser) {
  $browserid = db_insert(TBL_PREFIX.TBL_BROWSERS, "name", "'".$_POST['bname']."'");
} else {
  $browserid = $browser['id'];
}
// save OS id
$os = db_select(TBL_PREFIX.TBL_OS, "id", "name='".$_POST['bos']."'");
if (!$os) {
  $osid = db_insert(TBL_PREFIX.TBL_OS, "name", "'".$_POST['bos']."'");
} else {
  $osid = $os['id'];
}

/* create database entry ---------------------------------------------------- */
$fields  = "client_id,cache_id,os_id,browser_id,browser_ver,user_agent,";
$fields .= "ftu,scr_width,scr_height,vp_width,vp_height,";
$fields .= "sess_date,sess_time,fps,coords_x,coords_y,clicks_x,clicks_y,hovered,clicked"; 

$values  = "'". get_client_id()            ."',";
$values .= "'". $logid                     ."',";
$values .= "'". $osid                      ."',";
$values .= "'". $browserid                 ."',";
$values .= "'". (float) $_POST['bversion'] ."',";
$values .= "'". $_POST['bua']              ."',";
$values .= "'". (int) $_POST['ftu']        ."',";
$values .= "'". (int) $_POST['screenw']    ."',";
$values .= "'". (int) $_POST['screenh']    ."',";
$values .= "'". (int) $_POST['vpw']        ."',";
$values .= "'". (int) $_POST['vph']        ."',";
$values .= "NOW(),";
$values .= "'". (float) $_POST['time']     ."',";
$values .= "'". (int) $_POST['fps']        ."',";
$values .= "'". $_POST['xcoords']          ."',";
$values .= "'". $_POST['ycoords']          ."',";
$values .= "'". $_POST['xclicks']          ."',";
$values .= "'". $_POST['yclicks']          ."',";
$values .= "'". array_sanitize($_POST['elhovered']) ."',";
$values .= "'". array_sanitize($_POST['elclicked']) ."'";

$uid = db_insert(TBL_PREFIX.TBL_RECORDS, $fields, $values);
// send user ID back to the record script
echo $uid;
?>