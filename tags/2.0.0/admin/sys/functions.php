<?php
/**
 * (smt)2 CMS core functions.
 * @date 27/March/2009    
 */
define('REQUIRED', dirname(__FILE__));

// --------------------------------------------------------- (smt) constants ---
require REQUIRED.'/define.php';
require REQUIRED.'/messages.php';

// --------------------------------------------------------- K-means classes ---
require REQUIRED.'/class.cluster.php';
require REQUIRED.'/class.point.php';

// ------------------------------------------------------- (smt) backend API ---
require REQUIRED.'/functions.db.php';

/** 
 * Checks if server is ready to work with (smt) by comparing the server's $type version.
 * At least are required both PHP 5 and MySQL 5.
 * @param   string    $type       "php" or "mysql" by now
 * @param   string    $minReqVer  minimun system version (default: 5.0.0) 
 * @return  boolean               TRUE on sucess, or FALSE on failure 
 */
function check_systemversion($type, $minReqVer = "5.0.0") 
{
  switch (strtolower($type)) {
    case 'mysql':
      $ver = mysql_get_server_info();
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
 * Checks if a new (smt) version is released.
 * @return  int   server response: 1 (up to date), 2 (new version found), 3 (minor build released), 0 (connection error), -1 (parsing error) 
 */
function get_smt_releases()
{
  // connect to Web Service
  $ws = get_remote_webpage("http://smt.speedzinemedia.com/smt/versioncheck.php?v=".SMT_VERSION);
  
  return $ws['content'];
}

/** 
 * Displays a message about the installed (smt) version.
 * @return  string   message 
 */
function check_smt_releases()
{
  $dwnurl = "http://smt.speedzinemedia.com/smt/downloads.php";
  
  $code = get_smt_releases();
  
  switch ($code) {
    case -1:  // parsing/reading error
      $msg = '<p class="error">Error while retrieving new (smt) releases.</p>';
      break;
    case 0:   // connection error
    default:
      $msg = '<p class="error">Could not find new (smt) releases. If your Internet connection is OK, maybe the server is temporarily down.</p>';
      break;
    case 1:   // up to date
      $msg = '<p class="success">You are using the latest (smt) version: '.SMT_VERSION.'</p>';
      break;
    case 2:   // new version found
      $msg = '<p class="warning">A new (smt) version is available. <a href="'.$dwnurl.'">Please upgrade</a>.</p>';
      break;
    case 3:   // minor build released
      $msg = '<p class="warning">It seems that there is a <a href="'.$dwnurl.'">new (smt) build available</a>.</p>';
      break;
  }
  
  return $msg;
}

/**
 * Redirects the browser to a specified anchor on the page that sent a form.
 * @param   string    $id         HTML element id
 * @param   boolean   $success    no errors to display
 * @param   string    $customErr  if $success is false, type here your own custom message  
 */
function notify_request($id, $success, $customErr = "")
{
  $errorMessage = "An error occurred while processing your request.";
  if (!$success && !empty($customErr)) {
    $errorMessage = $customErr;
  }
  
  $_SESSION[ $id ] = ($success) ? 
                     '<p class="success">Data were processed successfully.</p>' :
                     '<p class="error">'.$errorMessage.'</p>';
  
  header("Location: ".dirname($_SERVER['PHP_SELF'])."/#".$id);
  // stop processing more PHP instructions
  exit;
}

/**
 * Redirects the browser to a specified anchor on the page that sent a form.
 * @param   string    $id   HTML element id
 * @param   string    $msg  message to display  
 */
function notify_request_warning($id, $msg)
{
  $_SESSION[ $id ] = '<p class="warning">'.$msg.'</p>';
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
 * @return string         Pretty-formatted client ID 
 * @param  string   $id   client ID 
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
 * @return string             The error page 
 * @param  string   $bodyText additional info to display on page body
 */
function error_webpage($bodyText = "") 
{  
  $webpage = '<html><head><title>Not found!</title></head><body>'.$bodyText.'</body></html>';
  
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
 * Removes empty items (both key and value) from an associative array.
 * @param   array  $input   array to sanitize
 * @return  array           Sanitized array
 */
function array_sanitize($input) 
{
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
 * Does a weighted sum for a given multidimensional array and computed weights.
 * @param   array  $inputArray  multidimensional array (matrix)
 * @param   array  $weights     weights 
 * @return  array               Weighted sum
 * @link    http://www.compapp.dcu.ie/~humphrys/PhD/e.html 
 */
function weighted_avg($inputArray, $weights) 
{
  $sumArray = array();
  
  foreach ($inputArray as $arrItem) {
    $sumArray[] = array_avg($arrItem) * count($arrItem)/max($weights);
  }
  
  return $sumArray;
}

/** 
 * Computes the average sum of an array.
 * @param   array  $inputArray   array or set of arrays
 * @return  float                Array average  
 */
function array_avg($inputArray) 
{
  $arrArgs = (func_num_args() === 1) ? $inputArray : func_get_args();
  $count = 0;
  
  foreach ($arrArgs as $arrItem) 
  {
    if (!is_array($arrItem)) { $arrItem = explode(",", $arrItem); }
    
    $sum += array_sum($arrItem)/count($arrItem);
    ++$count;
  }
  
  return round($sum/$count, 2);
}

/** 
 * Gets the array index that has the maximum value.
 * @param   array  $inputArray   array
 * @return  int                  Array index  
 */
function array_argmax($inputArray) 
{
  $max = max($inputArray);
  foreach ($inputArray as $key => $value) {
    if ($value == $max) { $maxIndex = $key; }
  }
  
  return $maxIndex;
}

/** 
 * Gets the array index that has the minimum value.
 * @param   array  $inputArray   array
 * @return  int                  Array index  
 */
function array_argmin($inputArray) 
{
  $min = min($inputArray);
  foreach ($inputArray as $key => $value) {
    if ($value == $min) { $minIndex = $key; }
  }
  
  return $minIndex;
}

/** 
 * Merges vertical and horizontal coordinates in a bidimensional point array.
 * @param   array  $xcoords        horizontal coordinates
 * @param   array  $ycoords        vertical coordinates
 * @param   array  $getDistances   if TRUE, the result array contains euclidean distances
 * @return  array                  2D points or euclidean distances
 */
function convert_points($xcoords, $ycoords, $getDistances = false) 
{
  // initialize points array
  $pointArray = array();
  // transform arrays in a single points array
  foreach ($xcoords as $i => $value) 
  {
    $p = new Point($value, $ycoords[$i]); 
    // check if next point exists 
    if ($xcoords[$i + 1] === null) { break; }
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
 * Computes K-means clustering for a given 2D array of mouse points.
 * @param   int    $k     number of clusters  
 * @param   array  $arr   points array
 * @return  array         clusters (center means and mean variances)
 */
function distributeOverClusters($k, $arr)
{
  // set 1 minute timeout for dealing with large vectors
  ini_set('max_execution_time', 60);

  $maxX = 0; $maxY = 0;
  foreach ($arr as $point) 
  {
    if ($point->x > $maxX) { $maxX = $point->x; }
    if ($point->y > $maxY) { $maxY = $point->y; }
  }
    
  for ($i = 0; $i < $k; ++$i) 
  {
    // initialize clusters centers randomly
    $center = new Point(rand(0, $maxX), rand(0, $maxY));
    // create clusters
    $clusters[] = new Cluster($center);
  }
  
  // now deploy points to closest center
  for ($a = 0; $a < 10; ++$a)         // 10 iterations is enough...
  {       
    foreach ($clusters as $cluster) {
      $cluster->points = array();     // reinitialize points
    }
    // compute best distance
    foreach ($arr as $pnt) 
    {
      $bestcluster = $clusters[0];
      $bestdist = $clusters[0]->avgPoint->getDistance($pnt);
      
      foreach ($clusters as $cluster) {
        $distance = $cluster->avgPoint->getDistance($pnt);
        if ($distance < $bestdist) {
          $bestcluster = $cluster;
          $bestdist = $distance;
        }
      }
      // add the point to the best cluster.
      $bestcluster->points[] = $pnt;
    }
    // recalculate the centers and sample variance
    foreach ($clusters as $cluster) {
      $cluster->calculateAverage($maxX, $maxY);
      $cluster->calculateVariance();
    }
    
  } // end loop
  
  return $clusters;
}

/* Katsavounidis et al. "A New Initialization Technique for Generalized
 * Lloyd Iteration", IEEE Signal Proc. Lett. 1 (10), 144-146, 1994. 
 */
function distributeOverClusters2($k, $arr)
{
  // set 1 minute timeout for dealing with large vectors
  ini_set('max_execution_time', 60);
  
  $maxX = 0; $maxY = 0; $maxNorm = 0; $maxIndex = 0;
  // init
  foreach ($arr as $i => $point) 
  {
    if ($point->x > $maxX) { $maxX = $point->x; }
    if ($point->y > $maxY) { $maxY = $point->y; }
    // calculate the L2 norm of all mouse points (2D vector)
    $norm = vectorNorm($point, "uL2");
    if ($norm > $maxNorm) { 
      $maxNorm = $norm;
      $maxIndex = $i; 
    }
  }
  
  $clusters = array();
  // choose the vector with the maximum norm as the first codeword
  $clusters[0] = new Cluster($arr[$maxIndex]);
  // check
  if ($k < 2) {
    $clusters->points = $arr;
    return $clusters;
  }
  /* Calculate the distance of all mouse points from the first codeword
   * and choose the vector with larger distance as the second codeword
   */  
  $clusters[1] = deployCluster($clusters[0]->avgPoint, $arr);
  
  /* With codebook of size > 2, compute the distance between any remaining
   * vector and all existing codewords
   */  
  for ($i = 2; $i < $k; ++$i) {
    $center = new Point(rand(0, $maxX), rand(0, $maxY));
    $clusters[$i] = deployCluster($center, $arr);
  }
  for ($a = 0; $a < 5; ++$a)         // 5 iterations is enough...
  {       
    foreach ($clusters as $cluster) {
      $cluster->points = array();     // reinitialize points
    }
    // compute best distance
    foreach ($arr as $pnt) 
    {
      $bestcluster = $clusters[0];
      $bestdist = $clusters[0]->avgPoint->getDistance($pnt);
      
      foreach ($clusters as $cluster) {
        $distance = $cluster->avgPoint->getDistance($pnt);
        if ($distance < $bestdist) {
          $bestcluster = $cluster;
          $bestdist = $distance;
        }
      }
      // add the point to the best cluster.
      $bestcluster->points[] = $pnt;
    }
    // recalculate the centers and sample variance
    foreach ($clusters as $cluster) {
      $cluster->calculateAverage($maxX, $maxY);
      $cluster->calculateVariance();
    } 
  } // end loop 
  return $clusters;
}

/** 
 * Assigns points to a cluster.
 * @param   array   $center   cluster center  
 * @param   array   $vectors  array of 2D points
 * @return  array             New cluster
 */
function deployCluster($center, $vectors)
{
  foreach ($vectors as $v) 
  {
    $distance = $center->getDistance($v);
    
    if ($distance > $bestDist) {
      $nextCluster = $v;
      $bestDist = $distance;
    }
  }
  $cluster = new Cluster($nextCluster);
  
  return $cluster;
}

/** 
 * Vector Norm calculation
 * @param   array   $array  input Array  
 * @param   string  $type   norm calculation type: "L1", "uL2", "sL2", "Linf"
 * @return  float           Vector Norm
 * @link http://w3.gre.ac.uk/~physica/phy3.00/theory/node97.htm 
 */
function vectorNorm($array, $type) 
{
  /* Note: mouse coordinates are positive values always, so we don't need 
   * to compute the absolute values first for cases "sL2", "Linf" and "Linf2".
   */
  switch ($type) 
  {
    /** 1-norm */
    case "L1":
      foreach ($array as $value) { $sum += abs($value); }
      $result = $sum;
      break;
        
    /** Unscaled 2-norm */  
    case "uL2":
    default:
      foreach ($array as $value) { $sum += $value*$value; }
      $result = sqrt($sum);
      break;
      
    /** Scaled 2-norm */
    case "sL2":
      $rmax = max($array);
      foreach ($array as $value) {
        $value /= $rmax; 
        $sum += $value*$value; 
      }
      $result = $rmax * sqrt($sum);
      break;
      
    /** Infinity norm */
    case "Linf":
      $result = max($array);
      break;
      
    /** Negative infinity norm */
    case "Linf2":
      $result = min($array);
      break;  
  }
  
  return $result;
}

/** 
 * Gets the full path to the current PHP file (protocol + domain + paths/to/file).
 * @param   boolean  $fullURI  append the query string, if any (default: false)  
 * @return  string             Full URL
 */
function getThisURLAddress($fullURI = false)
{
  // quick check:
  $url  = "http".(!empty($_SERVER['HTTPS']) ? "s" : null)."://".$_SERVER['SERVER_NAME'];
  $url .= ($fullURI) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];  

  return $url;
}

/** 
 * Gets the base path of a URL.
 * @param   string  $url  input URL  
 * @return  string        Base URL
 */
function getBase($url)
{
  // split url in dirs  
  $paths = explode("/", $url);
  // remove last element, so we do not have to worry about the query string (?var1=value1&var2=value2#anchor...)
  array_pop($paths);
  // and we have the BASE href
  $base = implode("/", $paths) . "/";  

  return $base;
}

/** 
 * Creates an external script element.
 * @param   object  $dom  DOMDocument  
 * @param   string  $url  script URL
 * @return  string        HTML element: <script type="text/javascript" src="$url"></script>
 */
function createExternalScript($dom, $url) 
{
  $js = $dom->createElement('script');
  $js->setAttribute('type', 'text/javascript');
  $js->setAttribute('src', $url);
  
  return $js;
}

/** 
 * Creates an inline script element.
 * @param   object  $dom      DOMDocument  
 * @param   string  $cdata    javascript code (should be wrapped in a CDATA section)
 * @return  string            HTML element: <script type="text/javascript">$cdata</script>
 */
function createInlineScript($dom, $cdata) 
{ 
  $js = $dom->createElement('script', $cdata);
  $js->setAttribute('type', 'text/javascript');

  return $js;
}

/** 
 * Creates an external stylesheet element.
 * @param   object  $dom  DOMDocument  
 * @param   string  $url  stylesheet URL
 * @return  string        HTML element: <link type="text/css" rel="stylesheet" href="$url" />
 */
function createExternalStyleSheet($dom, $url) 
{
  $css = $dom->createElement('link');
  $css->setAttribute('type', 'text/css');
  $css->setAttribute('rel', 'stylesheet');
  $css->setAttribute('href', $url);
  
  return $css;
}

/** 
 * Creates an inline stylesheet element.
 * @param   object  $dom     DOMDocument  
 * @param   string  $styles  CSS styles
 * @return  string           HTML element: <style type="text/css">$styles</style>
 */
function createInlineStyleSheet($dom, $styles) 
{
  $css = $dom->createElement('style', $styles);
  $css->setAttribute('type', 'text/css');
  
  return $css;
}

/** 
 * Creates a DIV element.
 * @param   object  $dom      DOMDocument  
 * @param   string  $id       DIV id 
 * @param   string  $content  DIV content (plain text) (default: none) 
 * @return  string            HTML element: <div id="$id">$content</div>
 */
function createDiv($dom, $id, $content = "") 
{
  $div = $dom->createElement('div', $content);
  $div->setAttribute('id', $id);
  
  return $div;
}

/** 
 * Checks if a javascript file exists in the DOM, 
 * by comparing the provided $source with the script's "src" attribute.
 * @param   object  $dom      DOMDocument  
 * @param   string  $source   JavaScript source attribute
 * @return  boolean           TRUE on succes or FALSE on failure
 */
function scriptExists($dom, $source)
{
  $scripts = $dom->getElementsByTagName("script"); 
  foreach ($scripts as $script) {
    $src = $script->getAttribute("src");
    if (strpos($src, $scriptSrc) !== false) {
      return true; 
    }
  }
  return false;
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
  $current = ext_name();
  // check priority
  $prioritized = get_exts_order();
  // loop through available sections
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


/** Additional head tags. Enable inserting custom tags on page head. */
$HEAD_ADDED = array();
/** 
 * Adds $element tags to all CMS extensions header.
 * @param   string  $element  HTML code to insert in the HEAD of any CMS section (<style>, <script>, etc.)
 * @global  array   $HEAD_ADDED
 */
function add_head($element) 
{
  global $HEAD_ADDED;
  if (!$element) return;
  
  $HEAD_ADDED[] = $element;
}

/** 
 * Displays a <noscript> warning message. Useful for those extensions that require JavaScript functionality.
 * @return  string          Message wrapped in a <noscript> tag
 * @param   string  $msg    custom warning message. Default: "Please enable JavaScript in order to work on this section."  
 */
function check_noscript($msg = "Please enable JavaScript in order to work on this section.") 
{
  return '<noscript><p class="warning">'.$msg.'</p></noscript>';
}

/** 
 * Count files in a dir. This function skip directories, and it is not recursive.
 * By now it is only used to check the cache logs. 
 * @param   string  $dir    the directory to read files from
 * @return  int             number fo files
 */
function count_dir_files($dir) 
{  
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
  $ahPasswordGenerator = array(
  	"C" => array('chars' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'min' => 4, 'max' => 6),
  	"S" => array('chars' => "!@()-_=+?*^&", 'min' => 1, 'max' => 2),
  	"N" => array('chars' => '1234567890', 'min' => 2, 'max' => 2)
  );
	// Create the meta-password
	$sMetaPassword = "";
	foreach ($ahPasswordGenerator as $cToken => $ahPasswordSeed) {
    $sMetaPassword .= str_repeat($cToken, rand($ahPasswordSeed['min'], $ahPasswordSeed['max']));
  }
	$sMetaPassword = str_shuffle($sMetaPassword);
	// Create the real password
	$arBuffer = array();
	for ($i = 0; $i < strlen($sMetaPassword); ++$i) {
    $arBuffer[] = $ahPasswordGenerator[(string)$sMetaPassword[$i]]['chars'][rand(0, strlen($ahPasswordGenerator[$sMetaPassword[$i]]['chars']) - 1)];	 
  }

	return implode("", $arBuffer);
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
?>