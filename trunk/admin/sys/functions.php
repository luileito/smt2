<?php
/**
 * smt2 CMS core functions.
 * @date 27/March/2009  
 * @rev 20/December/2009
 */
error_reporting(E_ALL | E_STRICT);

unregister_GLOBALS();

// ignore PHP strict notice if time zone has not been set in php.ini
$defaultTimeZone = @date_default_timezone_get();

if ($defaultTimeZone) {
  $location = $defaultTimeZone;
} else if (ini_get('date.timezone')) {
  $location = ini_get('date.timezone');
} else {
  $location = 'UTC';
}
// set date
date_default_timezone_set($location);

// load base files
define('REQUIRED', dirname(__FILE__));

// ---------------------------------------------------------- smt2 constants ---
require REQUIRED.'/define.php';
require REQUIRED.'/messages.php';
// --------------------------------------------------------------- utilities ---
require REQUIRED.'/class.domutil.php';
require REQUIRED.'/class.browser.php';
require REQUIRED.'/class.point.php';
// ------------------------------------------------------------ database API ---
require REQUIRED.'/functions.db.php';

/** 
 * Additional head tags. Enable inserting custom tags on page head.
 * @global array $_headAdded
 */
$_headAdded = array();

/** 
 * Checks if server is ready to work with smt2 by comparing the server's $type version.
 * At least are required both PHP 5 and MySQL 5.
 * @param   string    $type       "php" or "mysql", by now
 * @param   string    $minReqVer  minimun system version (default: 5.0.0) 
 * @return  boolean               TRUE on sucess, or FALSE on failure 
 */
function check_systemversion($type, $minReqVer = "5.0.0") 
{
  switch (strtolower($type)) {
    case 'mysql':
      $ver = mysql_get_client_info();
      break;
    case 'php':
      $ver = phpversion();
      break;
    default:
      break;
  }
  // $ver must be >= $minReqVer
  $status = version_compare($ver, $minReqVer, ">=");
  
  return $status;
}

/** 
 * Checks if a new smt2 version is released.
 * @return  int   Server response: 1 (up to date), 2 (new version found), 3 (minor build released), 0 (connection error), -1 (parsing error) 
 */
function get_smt_releases()
{
  // connect to Web Service
  $ws = get_remote_webpage("http://smt.speedzinemedia.com/smt/versioncheck.php?v=".SMT_VERSION);
  
  return $ws['content'];
}

/** 
 * Displays a message about the installed smt2 version.
 * @return  string   Message 
 */
function check_smt_releases()
{
  global $_displayType;
  
  $dwnurl = "http://smt.speedzinemedia.com/smt/downloads.php";
  
  $code = get_smt_releases();
  
  switch ($code) {
    case -1:  // parsing/reading error
      $type = $_displayType["ERROR"];
      $text = 'Error while retrieving new (smt)<sup>2</sup> releases.';
      break;
    case 0:   // connection error
    default:
      $type = $_displayType["ERROR"];
      $text = 'Could not find new (smt)<sup>2</sup> releases. If your Internet connection is OK, maybe the server is temporarily down.';
      break;
    case 1:   // up to date
      $type = $_displayType["SUCCESS"];
      $text = 'You are using the latest (smt)<sup>2</sup> version: '.SMT_VERSION;
      break;
    case 2:   // new version found
      $type = $_displayType["WARNING"];
      $text = 'A new (smt)<sup>2</sup> version is available. <a href="'.$dwnurl.'">Please upgrade</a>.';
      break;
    case 3:   // minor build released
      $type = $_displayType["WARNING"];
      $text = 'It seems that there is a <a href="'.$dwnurl.'">new (smt)<sup>2</sup> build available</a>.';
      break;
  }
  
  return display_text($type, $text);
}

/**
 * Displays a text paragraph on page.
 * @param   string    $type "warning", "error" or "success"
 * @param   string    $msg  message to display
 * @param   string    $elem DOM element to enclose message (default: p)
 */
function display_text($type, $msg, $elem = 'p')
{
  return '<'.$elem.' class="'.$type.'">'.$msg.'</'.$elem.'>';
}

/**
 * Redirects the browser to a specified anchor on the page that sent a form.
 * @param   string    $id         HTML element id
 * @param   boolean   $success    no errors to display
 * @param   string    $customErr  if $success is false, type here your own custom message  
 */
function notify_request($id, $success, $customErr = "")
{
  global $_displayType, $_notifyMsg;
  
  $errorMessage = $_notifyMsg["ERROR"];
  if (!$success && !empty($customErr)) {
    $errorMessage = $customErr;
  }
  
  $_SESSION[ $id ] = ($success) ? 
                     display_text($_displayType["SUCCESS"], $_notifyMsg["SAVED"])
                     :
                     display_text($_displayType["ERROR"],   $errorMessage);
  
  redirect( dirname($_SERVER['SCRIPT_NAME'])."/#".$id );
}

/**
 * Displays the message saved on current PHP session. Then the $_SESSION text is unset.
 * @param   string  $name   session variable name 
 */
function check_notified_request($name) 
{
  // get $name text from session
  if (isset($_SESSION[$name])) {
    echo $_SESSION[$name];
  }
  // unset session var
  unset($_SESSION[$name]);
}

/** 
 * Shows only the first $words of a text, plus a [...] symbol. 
 * @param   string  $text   text to trim
 * @param   int     $words  number of words to display (default: 5)
 * @return  string          The trimmed text   
 */
function trim_text($text, $words = 5) 
{
  $space = " ";  
  $text = explode($space, $text);
  
  $show = "";
  foreach ($text as $i => $str) {
    if ($i < $words) { 
      $show .= $str.$space; 
    }
  }
  // references of $i and the last array element remain 
  if ($i >= $words) {
    // add [...] if word count is indeed larger than $words
    $show .= $space."[...]"; 
  }

  return $show;
}

/** 
 * Assigns an unique identifier for each client machine. 
 * @return  string    Encoded client identifier   
 */
function get_client_id()
{
   if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
      $client_id =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR'] :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
               $_ENV['REMOTE_ADDR'] :
               "unknown" );
      $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
      reset($entries);
      while (list(, $entry) = each($entries)) {
         $entry = trim($entry);
         if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $id_list) ) {
            // see http://www.faqs.org/rfcs/rfc1918.html
            $private_id = array(
                                '/^0\./',
                                '/^127\.0\.0\.1/',
                                '/^192\.168\..*/',
                                '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                                '/^10\..*/'
                               );
   
            $found_id = preg_replace($private_id, $client_id, $id_list[1]);
   
            if ($client_id != $found_id) {
               $client_id = $found_id;
               break;
            }
         }
      }
   } else {
      $client_id =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR'] :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
               $_ENV['REMOTE_ADDR'] :
               "unknown" );
   }
   
   return base64_encode($client_id);
}

/** 
 * Masks a given client ID string.
 * @param  string   $id   client ID 
 * @return string         Pretty-formatted client ID 
 */
function mask_client($id)
{
  $hash = md5($id);
  $half = strlen($hash) / 2;
  
  return substr($hash, -$half, $half);
}


/** 
 * Gets URL contents within the HTTP server response header fields.
 * This function uses cURL to fetch remote pages. 
 * @param  string   $URL   web page URL
 * @param  array    $opts  custom cURL options  
 * @return array           Transfer information (the web page content is in the "content" array key)
 * @link  http://es2.php.net/manual/en/curl.constants.php
 * @link  http://es2.php.net/manual/en/function.curl-setopt.php
 */
function get_remote_webpage($URL, $opts = array())
{
  // basic options (regular GET requests)
  $options = array(
                    CURLOPT_URL            => $URL,
                    CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
                    CURLOPT_RETURNTRANSFER => true,   // return transfer as a string
                    CURLOPT_HEADER         => false,  // don't return headers
                    CURLOPT_ENCODING       => "",     // handle all encodings
                    CURLOPT_CONNECTTIMEOUT => 10,     // timeout on connect
                    CURLOPT_TIMEOUT        => 60,     // timeout on response
                    CURLOPT_SSL_VERIFYPEER => false,  // try to fetch SSL pages too
                    CURLOPT_SSL_VERIFYHOST => false
                  );

  /* cURL should follow redirections! 
   * But safe mode (deprecated) and open_basedir (useless) are incompatible
   * with CURLOPT_FOLLOWLOCATION.
   * Also see this solution: http://www.php.net/manual/en/function.curl-setopt.php#71313      
   */
  if (!ini_get('safe_mode') && !ini_get('open_basedir')) {
    $options[ CURLOPT_FOLLOWLOCATION ] = true;  // follow redirects
    $options[ CURLOPT_AUTOREFERER ]    = true;  // automatically set the Referer: field
    $options[ CURLOPT_MAXREDIRS ]      = 5;     // limit redirect loops
    
  }
  
  // add custom cURL options (e.g. POST requests, cookies, etc.)
  if (count($opts) > 0)
  {
    foreach ($opts as $key => $value) {
      $options[$key] = $value;
    }
  }
  
  $ch = curl_init();

  curl_setopt_array($ch, $options);

  $content  = curl_exec($ch);     // the Web page
  $transfer = curl_getinfo($ch);  // transfer information (http://www.php.net/manual/en/function.curl-getinfo.php)
  $errnum   = curl_errno($ch);    // codes: http://curl.haxx.se/libcurl/c/libcurl-errors.html
  $errmsg   = curl_error($ch);    // empty string on success

  curl_close($ch);

  // extend transfer info
  $transfer['errnum']  = $errnum;
  $transfer['errmsg']  = $errmsg;
  $transfer['content'] = $content;
  // $transfer['url'] is the final URL after redirections, if CURLOPT_FOLLOWLOCATION is set to true
  
  return $transfer;
}

/** 
 * Displays a default error page.
 * Used when a cached page is deleted, as well as when cURL cannot fetch a remote page. 
 * @param  string   $bodyText additional info to display on page body
 * @return string             The error page 
 */
function error_webpage($bodyText = "") 
{  
  $webpage = '<html><head><title>Error</title></head><body>'.$bodyText.'</body></html>';
  
  return $webpage; 
} 

/** 
 * Computes the frequency of each $input array member.
 * @param   mixed  $input        input string or array of strings to parse ($_POST vars are sent as strings)
 * @param   int    $threshold    frequencies (in percentage) under this $threshold will not be stored (default: 1)
 * @return  array                A sorted associative array in the form '[mostFrequentMember]=>frequency,...,[lessFrequentMember]=>frequency'
 */
function compute_frequency($input, $threshold = 1) 
{
  // convert $input in a real PHP array
  $input = (!is_array($input)) ? explode(",", $input) : $input;
  // count occurrences (array keys must be strings or integers)
  $unique = array_count_values($input); // Returns an associative array of values from $input as keys and their count as value. 
  // $hovered is an associative array(string => int)
  $unique = array_sanitize($unique);
  
  // exit if there are no data
  if (!$unique) return false;
  
  // compute sum
  $sum  = array_sum($unique);
  $data = array();
  // now calculate the frequency of each hovered element (in percentage)
  foreach ($unique as $k => $value) {
    $frequency = round(100*$value/$sum, 2);
    // store frecuencies above given threshold
    if ($frequency > $threshold) {
      $data[$k] = $frequency;
    } 
  }
  // order by frecuency
  arsort($data);

  return $data;
}

/** 
 * Removes empty items (both key and value) from an associative numeric array.
 * @param   mixed  $input   array or string to sanitize
 * @return  mixed           Sanitized array or string (used for widget tracking)
 */
function array_sanitize($input)
{
  $isString = false;
  
  if (!is_array($input)) { 
    $input = explode(",", $input);
    $isString = true; 
  }
  
  $temp = array();  
  foreach ($input as $key => $value) {
    // avoid buggy values
    $key = trim($key);
    $value = trim($value);
    // store valid data
    if (!empty($key) && !empty($value)) {
      $temp[$key] = $value;
    }
  }
  
  return ($isString) ? implode(",", $temp) : $temp;
}

/** 
 * Convert null values to empty strings. Used to generate valid JSON arrays.
 * @param   array  $input   array
 * @return  array           Parsed array
 */
function array_null($input)
{
  if (!is_array($input)) {
    $input = explode(",", $input);
  }
  
  $temp = array(); 
  foreach ($input as $key => $value) {
    // store valid data
    $temp[$key] = (!empty($value)) ? $value : 0;
  }
  
  return $temp;
}

/** 
 * Does a weighted sum for a given multidimensional numeric array and computed weights.
 * @param   array  $input     multidimensional array (matrix)
 * @param   array  $weights   weights 
 * @return  array             Weighted sum
 * @link    http://www.compapp.dcu.ie/~humphrys/PhD/e.html 
 */
function weighted_avg($input, $weights) 
{
  $sumArray = array();
  
  foreach ($input as $arrItem) {
    $sumArray[] = array_avg($arrItem) * count($arrItem) / max($weights);
  }
  
  return $sumArray;
}

/** 
 * Computes the average sum of a numeric array.
 * @param   array  $input   array or set of arrays (matrix)
 * @return  float           Array average
 */
function array_avg($input)
{
  return round( array_sum($input) / count($input), 2);
}

/**
 * Computes the average sum of a matrix, assuming that each row is a numeric array.
 * @param   array $matrix a set of arrays (matrix)
 * @return  float         matrix average value
 */
function matrix_avg($matrix)
{
  $sum = 0;
  $count = 0;

  foreach ($matrix as $arrItem)
  {
    if (!is_array($arrItem)) { $arrItem = explode(",", $arrItem); }

    $sum += array_avg($arrItem);
    // note that this is an accumulative sum
    ++$count;
  }

  return round( $sum/$count, 2 );
}

/**
 * Computes the variance of a numeric array.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_sd($input)
{
  $variance = 0;
  $mean = array_avg($input);
  foreach ($input as $elem) {
    $variance += ($elem - $mean) * ($elem - $mean);
  }

  return round( sqrt($variance/count($input)), 2 );
}

/**
 * Computes the standard deviation of a matrix, assuming that each row is a numeric array.
 * @param   array $matrix a set of arrays (matrix)
 * @return  float         matrix average value
 */
function matrix_sd($matrix)
{
  $sd = 0;
  $count = 0;

  foreach ($matrix as $arrItem)
  {
    if (!is_array($arrItem)) { $arrItem = explode(",", $arrItem); }

    $sd += array_sd($arrItem);

    // note that we can have more than one input array
    ++$count;
  }
  
  return round( $sd/$count, 2 );
}

/** 
 * Gets the array index that has the maximum value.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_argmax($input)
{
  $max = max($input);
  foreach ($input as $key => $value)
  {
    if ($value == $max) {
      $maxIndex = $key;
      break;
    }
  }
  
  return $maxIndex;
}

/** 
 * Gets the array index that has the minimum value.
 * @param   array  $input   array
 * @return  int             Array index
 */
function array_argmin($input)
{
  $min = min($input);
  foreach ($input as $key => $value)
  {
    if ($value == $min) {
      $minIndex = $key;
      break;
    }
  }
  
  return $minIndex;
}

/**
 * Denests nested arrays within the given array.
 * @autor DZone Snippets
 * @link  http://snippets.dzone.com/posts/show/4660
 */
function array_flatten($input)
{
  $i = 0;
  while ($i < count($input))
  {
    if (is_array($input[$i])) {
      array_splice($input, $i, 1, $input[$i]);
    } else {
      ++$i;
    }
  }

  return $input;
}

/** 
 * Merges vertical and horizontal coordinates in a bidimensional point array.
 * Stops coordinates (hesitations) are removed. 
 * @param   array  $xcoords        horizontal coordinates
 * @param   array  $ycoords        vertical coordinates
 * @param   array  $getDistances   if TRUE, the result array contains euclidean distances
 * @return  array                  2D points or euclidean distances array
 */
function convert_points($xcoords, $ycoords, $getDistances = false) 
{
  // initialize points array
  $pointArray = array();
  // check for illegal offsets on $coords
  $maxCount = count($xcoords) - 1;
  // transform arrays in a single points array
  foreach ($xcoords as $i => $value) 
  {
    $p = new Point($value, $ycoords[$i]); 
    // check if next point exists 
    if ($i >= $maxCount) { break; }
    // ok
    $q = new Point($xcoords[$i + 1], $ycoords[$i + 1]);
    $distance = $p->getDistance($q);
    // check
    if ($getDistances) {
      $pointArray[] = $distance;
    } else {
      // append point to the points array, discarding null distances
      if ($distance > 0) { $pointArray[] = $p; }
    }
  }
  
  return $pointArray;
}

/**
 * Counts the number of mouse clicks.
 * Drag and drop traces are removed.
 * @param   array  $xclicks   horizontal click coordinates
 * @param   array  $yclicks   vertical click coordinates
 * @return  int               number of clicks
 */
function count_clicks($xclicks, $yclicks)
{
  $numClicks = 0;
  
  // check
  if (!is_array($xclicks)) { $xclicks = array_null($xclicks); }
  if (!is_array($yclicks)) { $yclicks = array_null($yclicks); }
  
  $maxCount = count($xclicks) - 1;
  // transform points
  foreach ($xclicks as $i => $value)
  {
    $p = new Point($value, $yclicks[$i]);
    // check if next point exists
    if ($i >= $maxCount) { break; }

    $q = new Point($xclicks[$i + 1], $yclicks[$i + 1]);
    if ($p->getDistance($q) > 0 && !$q->x) {
      $numClicks++;
    }
  }

  return $numClicks;
}
      
/**
 * Makes an HTTP 1.1 compliant redirect.
 * Absolute URLs are required, though all modern browsers support relative URLs.
 * @param   string    $path  where to go to, starting at server root (default: none)
 */
function redirect($path = "")
{
  $url = get_server_URL();
  
  if (empty($path)) { $path = $url; }
  // check that server url is on the $path argument
  if (strpos($path, $url) === false) { $path = $url.$path; }
  
	header("Location: ".$path);
	exit;
}

/**
 * Gets the URL of current server (protocol + domain).
 * @return  string             Full URL
 */
function get_server_URL()
{
  //$protocol = "http://";
  $protocol = "http" . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? "s" : null) . "://";

  $host = $_SERVER['HTTP_HOST']; // reliable in virtual hosts
  if (empty($host)) {
    $host = $_SERVER['SERVER_NAME'];
  }
  
  return $protocol.$host;
}

/**
 * Gets the full path to the current PHP file (protocol + domain + paths/to/file).
 * @param   boolean  $fullURI  append the query string, if any (default: false)
 * @return  string             Full URL
 */
function get_current_URL($fullURI = false)
{
  // quick check:
  $url  = get_server_URL();
  $url .= $_SERVER['SCRIPT_NAME'];
  if ($fullURI) { $url .= '?'.$_SERVER['QUERY_STRING']; }

  return $url;
}

/** 
 * Gets the base path of a URL.
 * @param   string  $url  input URL  
 * @return  string        Base URL
 */
function get_base($url)
{
  // split url in dirs
  $paths = explode("/", $url);
  // short URLs like http://server.com should be fixed
  if (count($paths) > 3) {
    // remove last element, so we do not have to worry about the query string (?var1=value1&var2=value2#anchor...)
    array_pop($paths);
  }
  // and we have the BASE href
  $base = implode("/", $paths) . "/";
  
  return $base;
}

/** 
 * Gets installed extensions priorities.
 * @return  array   Array with keys: dir name (string) => order priority (int)
 */
function get_exts_order() 
{
  $exts = db_select_all(TBL_PREFIX.TBL_EXTS, "*", "1");
  
  foreach ($exts as $ext) {
    $priority[ $ext['dir'] ] = (int) $ext['priority'];
  }
  // sort maintaining index association
  if (max($priority) > 0) { asort($priority); }

  return $priority;
}

/** 
 * Gets all available CMS sections.
 * @return  array   Array of strings (sections)
 */
function ext_available() 
{
  $dir = INC_PATH.'ext';
  $ext = array();
  if ($handle = opendir($dir)) 
  {
    while (false !== ($file = readdir($handle))) {
      // look for available module extensions
      if ($file != "." && $file != ".." && is_dir($dir.'/'.$file)) {
        $ext[] = $file;
      }
    }
    closedir($handle);
  }
  
  return $ext;
}

/** 
 * Gives format to CMS sections. 
 * @return  string  Formatted output list (LI elements)
 */
function ext_format()
{
  if (!isset($_SESSION['allowed'])) return false;
  
  $current = ext_name();
  // check priority
  $prioritized = get_exts_order();
  // loop through available sections
  $list = "";
  foreach ($prioritized as $dir => $priority) 
  { 
    if (!in_array($dir, $_SESSION['allowed'])) { continue; }
    
    $css = ($current == $dir) ? ' class="current"' : null;
    $href = ADMIN_PATH.'ext/'.$dir.'/';
    $list .= '<li'.$css.'><a href="'.$href.'">'.ucfirst(filename_to_str($dir)).'</a></li>';
  }
  
  return $list;
}

/** 
 * Gets the current CMS extension name.
 * @return  string    Section name
 */
function ext_name() 
{
  $ext = explode("/", dirname($_SERVER['PHP_SELF']));
  
  return $ext[ count($ext) - 1 ];
}

/** 
 * Assigns a valid filename to a given string: only alphanumeric chars. Spaces are converted to dashes.
 * @param   string  $string   input string 
 * @return  string            Normalized String
 */
function str_to_filename($string) 
{
  // remove non alphanumeric chars
  $string = preg_replace('/[^a-z0-9A-Z\s]+/', '', strtolower($string));
  // now convert spaces to dashes
  $string = str_replace(" ", "-", $string);
  
  return $string;
}

/** 
 * Reverse function for str_to_filename. Dashes are converted to spaces.
 * @param   string  $string   normalized String 
 * @return  string            Output String
 */
function filename_to_str($string) 
{
  // now convert dashes to spaces 
  $string = str_replace("-", " ", $string);
  
  return $string;
}

/** 
 * Adds $element tags to all CMS extensions header.
 * @param   mixed   $element  HTML code to insert in the HEAD of any CMS section (<style>, <script>, etc.). Can be a single string or an Array
 * @global  array   $_headAdded
 */
function add_head($element) 
{
  global $_headAdded;
  
  if (!$element) return;

  if (is_array($element)) {
    foreach ($element as $value) {
      $_headAdded[] = $value;
    }
  } else {
    $_headAdded[] = $element;
  }
}

/** 
 * Displays a <noscript> warning message. Useful for those extensions that require JavaScript functionality.
 * @param   string  $msg    custom warning message. Default: "Please enable JavaScript in order to work on this section."
 * @return  string          Message wrapped in a <noscript> tag   
 */
function check_noscript($msg = "") 
{
  global $_displayType, $_notifyMsg;
  
  if (empty($msg)) $msg = $_notifyMsg["NOSCRIPT"];
  
  return '<noscript>'.display_text($_displayType["WARNING"], $msg).'</noscript>';
}

/** 
 * Count files in a dir. This function skip directories, and it is not recursive.
 * By now it is only used to check the cache logs. 
 * @param   string  $dir    the directory to read files from
 * @return  int             Number of files
 */
function count_dir_files($dir) 
{  
  $count = 0;
  if ($handle = opendir($dir)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != ".." && is_file($dir.'/'.$file)) {
        $count++;
      }
    }
    closedir($handle);
  }
  
  return $count;
}

/** 
 * Verifies that current login has admin privileges.
 * Note that various admin users can coexist on the CMS.
 * @return  boolean   TRUE on sucess, or FALSE on failure
 */
function is_admin() 
{
  if (!isset($_SESSION['login'])) return false;
  
  // get admin role_id
  $user = db_select(TBL_PREFIX.TBL_USERS, "role_id", "login='".$_SESSION['login']."'");
  return ( (int) $user['role_id'] === 1 );
}

/** 
 * Verifies that current login is the superadmin user.
 * @return  boolean   TRUE on sucess, or FALSE on failure
 */
function is_root() 
{
  if (!isset($_SESSION['login'])) return false;
  
  // get root role_id
  $user = db_select(TBL_PREFIX.TBL_USERS, "id", "login='".$_SESSION['login']."'");
  return ( (int) $user['id'] === 1 );
}

/** 
 * Gets all allowed CMS extensions for the current user.
 * @return  array   Array of strings (sections)
 */
function is_allowed() 
{
  // check current user's role
  if ($_SESSION['role_id'] > 0)
  {
    $user = db_select(TBL_PREFIX.TBL_USERS, "role_id", "login='".$_SESSION['login']."'");
    if ( (int) $user['role_id'] !== 1 ) 
    {
      $current = ext_name();
      // check if current section is allowed
      return (strpos($current, $_SESSION['ext_allowed']) !== false);
    } else {
      return true;
    }
  }
  else 
  {
    return false;
  }
}
   
/**
 * Random Password Generator.
 * @autor Charlie
 * @link http://snippets.dzone.com/user/Charlie 
 * @version 0.1.0 - 2006-02-14 
 */
function generate_password()
{
  $pwd = array(
  	"C" => array('chars' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'min' => 4, 'max' => 6),
  	"S" => array('chars' => "!@()-_=+?*^&", 'min' => 1, 'max' => 2),
  	"N" => array('chars' => '1234567890', 'min' => 2, 'max' => 2)
  );
	// Create the meta-password
	$meta = "";
	foreach ($pwd as $cToken => $seed) {
    $meta .= str_repeat($cToken, rand($seed['min'], $seed['max']));
  }
	$meta = str_shuffle($meta);
	// Create the real password
	$buffer = array();
	for ($i = 0; $i < strlen($meta); ++$i) {
    $buffer[] = $pwd[(string)$meta[$i]]['chars'][rand(0, strlen($pwd[$meta[$i]]['chars']) - 1)];
  }

	return implode("", $buffer);
}

/**
 * Checks if an email address is valid. 
 * The chars # $ % & ' * + / = ? ^ ` { | } ~ are theoretically allowed on the local part,
 * but in practice they are discarded.  
 * @param   string    $email  email to check 
 * @return  boolean           TRUE on sucess, or FALSE on failure   
 * @link    http://tools.ietf.org/html/rfc5321
 * @link    http://tools.ietf.org/html/rfc5322  
 */
function is_email($email)
{       
  return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email);
}

/**
 * Searches DNS for MX records corresponding to user's email account hostname. 
 * @param   string    $email  user email 
 * @return  boolean           TRUE on sucess, or FALSE on failure   
 */
function email_exists($email)
{
  if (!is_email($email)) { return false; }
  
  list($user, $domain) = split("@", $email);
  if (function_exists('getmxrr') && getmxrr($domain, $MXHost)) {
    return true;
  } else {
    return (fsockopen($domain, 80, $errno, $errstr, 30));
  }
}

/**
 * Stops executing a PHP script, displaying a reason for the error.
 * @param   string    $text  message
 */
function die_msg($text = "")
{
  if (!empty($text)) { $text = ": " . $text; }
  
  die("<strong>Error</strong>".$text);
}

/**
 * Pad with zeros a number.
 * @param int $num      input number
 * @param int $numZeros number of zeros
 */
function pad_number($num, $numZeros)
{
  return sprintf("%0".$numZeros."d", $num);
}

/** Emulates register_globals off. */
function unregister_GLOBALS()
{
    if (!ini_get('register_globals')) {
        return;
    }

    // might want to change this perhaps to a nicer error
    if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
        die('GLOBALS overwrite attempt detected');
    }

    // variables that shouldn't be unset
    $noUnset = array('GLOBALS',  '_GET',
                     '_POST',    '_COOKIE',
                     '_REQUEST', '_SERVER',
                     '_ENV',     '_FILES');

    $input = array_merge($_GET,    $_POST,
                         $_COOKIE, $_SERVER,
                         $_ENV,    $_FILES,
                         isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

    foreach ($input as $k => $v) {
        if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
            unset($GLOBALS[$k]);
        }
    }
}

/**
 * Gets a SQL-alike string with all cache IDs that are related to the same URL.
 * @param   int     Log cache ID
 * @return  string  SQL query
 */
function get_common_url($pageId)
{
  $common = db_select(TBL_PREFIX.TBL_CACHE, "url", "id = '".$pageId."'");
  $moreId = db_select_all(TBL_PREFIX.TBL_CACHE, "id", "url = '".$common['url']."'");
  // merge values
  $moreId = array_flatten($moreId);
  // set query
  $merge = "";
  foreach ($moreId as $k => $value) {
    if ($value != $pageId)
      $merge .= " OR cache_id='".$value."' ";
  }

  return $merge;
}
?>