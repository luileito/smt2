<?php
// check data first (exclude registered users)
if (empty($_POST) || isset($_COOKIE['smt-usr'])) die(":(");

require_once '../config.php';

$URL = $_POST['url'];

// check proxy requests
$pattern = "proxy/index.php?url=";
if (strpos($URL, $pattern)) {
  list($remove, $URL) = explode($pattern, $URL);
  $URL = base64_decode($URL);
}

// get remote webpage
$request = get_remote_webpage(
                                $URL,
                                array( CURLOPT_COOKIE => $_POST['cookies'] )
                             );

$webpage = utf8_encode($request['content']);

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
      $cache_id = $cachelog['id'];
      $parse = false;
    } else {
      // cache days expired
      $parse = true;
    }
  } else {
    // cache is disabled
    $parse = true;
  }
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
    remove_smt_scripts($dom);
    // set HTML log name
    $date = date("Ymd-His");
    $ext = ".html";
    // "March 10th 2006 @ 15h 16m 08s" should create the log file "20060310-151608.html" 
    $htmlfile = (!is_file(CACHE_DIR.$date.$ext)) ? $date.$ext : $date.'-'.mt_rand().$ext;
    // store (UTF-8 encoded) log
    $dom->saveHTMLFile(CACHE_DIR.$htmlfile);
    // insert new row on TBL_CACHE and look for inserted id
    $cache_id = db_insert(TBL_PREFIX.TBL_CACHE, 
                       "file, url, layout, title, saved", 
                       "'".$htmlfile."', '".$URL."', '".$_POST['layout']."','".$_POST['urltitle']."', NOW()");
}

// verify
if (!isset($cache_id)) exit;

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
// save domain id
$domain = url_get_domain($URL);
$d = db_select(TBL_PREFIX.TBL_DOMAINS, "id", "domain='".$domain."'");
if (!$d) {
  $did = db_insert(TBL_PREFIX.TBL_DOMAINS, "domain", "'".$domain."'");
} else {
  $did = $d['id'];
}

/* create database entry ---------------------------------------------------- */
$fields  = "client_id,cache_id,domain_id,os_id,browser_id,browser_ver,user_agent,";
$fields .= "ftu,ip,scr_width,scr_height,vp_width,vp_height,";
$fields .= "sess_date,sess_time,fps,coords_x,coords_y,clicks,hovered,clicked"; 

$values  = "'". $_POST['client']               ."',";
$values .= "'". $cache_id                      ."',";
$values .= "'". $did                           ."',";
$values .= "'". $osid                          ."',";
$values .= "'". $browserid                     ."',";
$values .= "'". (float) $browser->getVersion() ."',";
$values .= "'". $browser->getUserAgent()       ."',";

$values .= "'". (int) $_POST['ftu']            ."',";
$values .= "'". get_ip()                       ."',";
$values .= "'". (int) $_POST['screenw']        ."',";
$values .= "'". (int) $_POST['screenh']        ."',";
$values .= "'". (int) $_POST['pagew']          ."',";
$values .= "'". (int) $_POST['pageh']          ."',";

$values .= "NOW(),";
$values .= "'". (float) $_POST['time']         ."',";
$values .= "'". (int)   $_POST['fps']          ."',";
$values .= "'". $_POST['xcoords']              ."',";
$values .= "'". $_POST['ycoords']              ."',";
$values .= "'". $_POST['clicks']               ."',";
$values .= "'". array_sanitize($_POST['elhovered']) ."',";
$values .= "'". array_sanitize($_POST['elclicked']) ."'";

$uid = db_insert(TBL_PREFIX.TBL_RECORDS, $fields, $values);
// send user ID back to the record script
echo $uid;
?>
