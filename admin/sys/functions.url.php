<?php
/**
 * Makes an HTTP 1.1 compliant redirect.
 * Absolute URLs are required, though all modern browsers support relative URLs.
 * @param   string    $path  where to go to, starting at server root (default: none)
 */
function url_redirect($path = "")
{
  $url = url_get_server();
  
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
function url_get_server()
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
function url_get_current($fullURI = false)
{
  // quick check:
  $url  = url_get_server();
  $url .= $_SERVER['SCRIPT_NAME'];
  if ($fullURI && $_SERVER['QUERY_STRING']) { $url .= '?'.$_SERVER['QUERY_STRING']; }

  return $url;
}

/** 
 * Gets the base path of a URL.
 * @param   string  $url  input URL  
 * @return  string        Base URL
 */
function url_get_base($url)
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
 * Gets the domain (host) name of a given URL.
 * @param   string  $url  input URL  
 * @return  string        Domain
 */
function url_get_domain($url)
{
  $parts = parse_url($url);
  
  return isset($parts['host']) ? $parts['host'] : "";
}
?>
