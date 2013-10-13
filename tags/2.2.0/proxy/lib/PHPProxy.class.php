<?php

require_once(dirname(__FILE__) . '/../conf/config.php');
require_once(dirname(__FILE__) . '/Logger.class.php');
require_once(dirname(__FILE__) . '/CurlConnector.class.php');
require_once(dirname(__FILE__) . '/HTMLParser.class.php');

/**
 * PHP Proxy class.
 */
class PHPProxy {
	
	// Internal array to store cookie name/value pairs.
	// Same as $_COOKIE superglobal except this will be a buffer between remote page and browser.
	var $cookies = array();
	
	// Internal array to store POST data
	var $post = array();
	
	// An array of files to cleanup once cURL has completed
	var $cleanup_files = array();
	
	// Default script options - overrideable by values in session
	// accept_cookies: whether to accept cookies on the client side
	// cookies_session_only: whether to force all cookies to be session only
	// include_navbar: whether to include the HTML navbar in pages
	// navbar_sticky: whether the nav bar should be 'sticky' by default
	// strip_script: whether to strip out <script> tags or not
	var $opts = array(
		'accept_cookies' => true,
		'cookies_session_only' => true,
		'include_navbar' => false,
		'navbar_sticky' => false,
		'strip_script' => false
	);
	
	// The headers the script is NOT allowed to pass through from the remote server.
	// note: content length and content type are set by this script so should be included
	// here.
	var $disallowed_headers = array(
		'set-cookie', 'content-length', 'content-type', 'transfer-encoding', 'location',
		'expires', 'pragma', 'cache-control'
	);
	
	// An array of protocols that this script supports.
	var $supported_protocols = array(
		'http', 'https'
	);

	function __construct($url, $username = NULL, $password = NULL) {
		session_start();
		
		foreach($this->opts as $key => $value) {
			if (array_key_exists('pref_' . $key, $_SESSION)) {
				$this->opts[$key] = $_SESSION['pref_' . $key];
			}
		}
	
		$this->log = new Logger();
		$this->url = $url;
		
		if (strlen($url) % 4 == 0) {
			$decoded = base64_decode($url);
			if ($decoded !== FALSE) {
				$this->url = trim($decoded);
			}
		}
		
		if (strpos($this->url, '://') === FALSE) {
			$this->url = 'http://' . $this->url;
		}
		
		$this->appendQueryString();
		
		$this->local_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		
		$this->log->info('URL to connect to: ' . $this->url);
		
		$msg = $this->checkAccess();
		if ($msg !== TRUE) {
			$this->log->warn('Access denied for reason: ' . $msg);
			$_SESSION['error'] = $msg;
			header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/access-denied.php');
			die();
		}
		
		if ($username !== NULL) {
			$this->username = $username;
			$this->password = $password;
			
			$this->storeUsernamePasswordInfo();
		}
		else {
			$this->loadUsernamePasswordInfo();
		}
		
		$this->connector = new CurlConnector($this, $this->url);
		$this->htmlparser = new HTMLParser($this);
	}
	
	/** 
	 * Entry point for the script. Handles the current request
	 * by decoding request parameters, cookies etc and writes
	 * the output out to the current page.
	 */
	function handleRequest() {
		if (isset($this->username)) {
			$this->connector->setLogin($this->username, $this->password);
		}
		
		$this->setReferer();
		$this->setPostParams();
		$this->setFiles();
		$this->setCookies();
		
		if (!empty($this->post)) {
			$this->log->debug(sprintf('Sending POST request; %d post vars', sizeof($this->post)));
			$this->connector->setPostInfo($this->post);
		}
		
		if ($this->connector->connect() === FALSE) {
			$_SESSION['error'] = $this->connector->getError();
			session_commit();
			header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/error.php');
			die();
		}

		$result = $this->connector->getOutput();
		$headers = $this->connector->getHeaders();
		$contentType = $headers['content-type'];
		
		foreach($this->cleanup_files as $file) {
			$this->log->debug("Removing '$file'");
			unlink($file);
		}
		
		if (strstr($contentType, 'html') !== FALSE) {
		  //$result = mb_convert_encoding($result, "UTF-8", "auto");
		  $result = utf8_decode($result);
		  // add (smt) tracking
		  $result = $this->htmlparser->includeTracking($result);
		  // proxify request
			$html = $this->htmlparser->parseHtml($result);
			
			if ($this->opts['include_navbar'] === TRUE) {
				$this->includeNavbar($html);
			}
			
			echo $html;
		}
		elseif (strstr($contentType, 'text/css') !== FALSE) {
			$css = $this->htmlparser->parseCss($result);
			
			echo $css;
		}
		
		$this->connector->disconnect();
	}
	
	/**
	 * Callback function from the connector to handle headers before the body 
	 * has been written out. This will return TRUE if the body is to be buffered,
	 * or FALSE if the content is binary and can be output directly.
	 */
	function handleHeaders($headers) {
		$httpCode = $this->connector->getHttpCode();
		$contentType = $headers['content-type'];
		
		// Set cookies first in case location header redirects us and we lose cookie info
		if ($this->opts['accept_cookies'] === TRUE) {
			$this->convertAndSetCookies($headers);
		}
		
		if (array_key_exists('location', $headers)) {
			// Convert the URL because some scripts don't send the full URL (in violation of the RFC, I believe...)
			$location = $headers['location'];
			if (strpos($location, INDEX_FILE_NAME . '?' . URL_PARAM_NAME) === FALSE) {
				$location = $this->htmlparser->convertUrl($location);
			}
			else {
				$location = $this->local_url . '?' . URL_PARAM_NAME . '=' . base64_encode($location);
			}
			$this->log->info('Redirecting to: ' . $location);
			header('Location: ' . $location);
			die();
		}
		
		if (array_key_exists('www-authenticate', $headers)) {
			$this->log->info('Site requires authentication!');
			$matches = array();
			preg_match('/realm="(.*?)"/', $headers['www-authenticate'], $matches);
			$realm = '';
			if (sizeof($matches) > 1) {
				$realm = $matches[1];
			}
			header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/basic-auth.php?' . URL_PARAM_NAME . '=' . base64_encode($this->url) . '&realm=' . urlencode($realm));
			die();
		}
		
		header('HTTP/1.1 ' . $httpCode);
		header('Content-Type: ' . $contentType);
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', FALSE);
		header('Pragma: no-cache');
		
		foreach ($headers as $key => $val) {
			if (in_array($key, $this->disallowed_headers)) continue;
			
			header($key . ': ' . $val);
		}
		
		$this->log->debug(sprintf('HTTP code is: [%s]', $httpCode));
		$this->log->debug(sprintf('Content type is: [%s]', $contentType));
	
		if (strstr($contentType, 'html') !== FALSE || strstr($contentType, 'text/css') !== FALSE) {
			return TRUE; // Buffer the content for HTML and CSS
		}
		else {
			if (! array_key_exists('content-disposition', $headers)) {
				header('Content-Disposition: inline; filename="' . basename($this->url) . '"');
				$this->log->debug(sprintf('Added Content-Disposition header for filename [%s]', basename($this->url)));
			}
			return FALSE; // Binary -- do not buffer, output directly
		}
	}
	
	/**
	 * Sets a user preference with the specified name and value.
	 */
	function setPref($name, $value) {
		// Convert to boolean if value is "true" or "false"
		if ($value == 'true') {
			$value = TRUE;
		}
		elseif ($value == 'false') {
			$value = FALSE;
		}
		
		$this->log->debug("Setting pref [$name] = [$value]");
		$_SESSION['pref_' . $name] = $value;
	}
	
	/**
	 * This function converts cookies for the proxy, and sends them to the user.
	 */
	private function convertAndSetCookies($headers) {
		$cookies = $headers['set-cookie'];
		
		if ($cookies == NULL) {
			$this->log->debug('No cookies sent with request');
			return;
		}
		
		if (! is_array($cookies)) {
			$cookies = array($cookies);
		}
		
		// Opts shouldn't be different for all cookies set for a domain, so just
		// maintain a separate array.
		$opts = array('expires' => 0, 'path' => '', 'domain' => '', 'secure' => '', 'httponly' => '');
		
		foreach ($cookies as $cookie) {
			$this->log->debug('Extracting cookie: ' . $cookie);
			
			$extracted = explode(';', $cookie);
			
			foreach ($extracted as $val) {
				$val = trim($val);
				if (strpos($val, '=') === FALSE) {
					$opts[] = $val;
					continue;
				}
				$name = trim(substr($val, 0, strpos($val, '=')));
				
				if (in_array($name, array('expires', 'domain', 'path'))) {
					$opts[$name] = $val;
				}
				else {
					$this->cookies[$name] = trim(substr($val, strpos($val, '=') + 1));
				}
			}
		}
		
		$flattened = '';
		foreach($this->cookies as $name=>$val) {
			$flattened .= '; ' . $name . '=' . $val;
		}
		$flattened = substr($flattened, 2);
		$cookieName = $this->sanitize($this->getBaseUrl()) . '_cookie';
		
		$this->log->debug(sprintf('Setting cookie name [%s], value [%s]', $cookieName, $flattened));
		
		$expires = $this->opts['cookies_session_only'] === TRUE ? 0 : ($opts['expires'] == '') ? 0 : strtotime($opts['expires']);
		
		setcookie($cookieName, base64_encode($flattened), $expires);
	}
	
	/**
	 * Retrieve the Base URL (i.e. the domain name)
	 */
	private function getBaseUrl() {
		$baseUrl = $this->url;
		
		// Strip protocol
		if (strpos($baseUrl, '://') !== FALSE) {
			$baseUrl = substr($baseUrl, strpos($baseUrl, '://') + 3);
		}
		
		if (strpos($baseUrl, '/') !== FALSE) {
			$baseUrl = substr($baseUrl, 0, strpos($baseUrl, '/'));
		}
		
		//$this->log->debug('Base URL is: ' . $baseUrl);
		
		return $baseUrl;
	}
	
	/**
	 * Sets the cURL referer to be the referer of the current page provided that 
	 */
	private function setReferer() {
		$referer = $_SERVER['HTTP_REFERER'];
		$matches = array();
		preg_match('/' . URL_PARAM_NAME . '=(.*?)(&|$)/', $referer, $matches);
		
		if (sizeof($matches) > 0) {
			$url = base64_decode($matches[1]);
		}
		else {
			$url = $referer;
		}
		
		if (!empty($url)) {
			$this->log->debug('Setting referer to: ' . $url);
			$this->connector->setReferer($url);
		}
		else {
			$this->log->debug('Referer is empty.');
		}
	}
	
	/**
	 * Loads all cookies from the user's browser and sets them in the current
	 * curl request. Also stores them in the cookies member variable.
	 */
	private function setCookies() {
		$this->log->debug(sprintf('%d cookies sent by user', sizeof($_COOKIE)));
		
		$cookieName = $this->sanitize($this->getBaseUrl()) . '_cookie';
		
		foreach ($_COOKIE as $name => $cookie) {
			if ($cookieName != $name) {
				continue;
			}
			
			$decoded = base64_decode($cookie);
			
			$this->log->debug('Sending cookie: ' . $name . ', value: ' . $decoded);
			
			$this->connector->setCookie($decoded);
			
			$arr = explode('; ', $decoded);
			foreach ($arr as $vals) {
				$key = substr($vals, 0, strpos($vals, '='));
				$val = substr($vals, strpos($vals, '=') + 1);
				
				$this->cookies[$key] = $val;
			}
		}
	}
	
	/**
	 * Sets the POST parameters for the request. This basically extracts all
	 * POST parameters from the current request and passes them through 
	 * to CURL.
	 */
	private function setPostParams() {
		if (sizeof($_POST) == 0) {
			return;
		}
		
		foreach($_POST as $key => $val) {
			if (get_magic_quotes_gpc() == 1) {
				$val = stripslashes($val);
			}
			$this->post[$key] = $val;
		}
	}
	
	/**
	 * Similar to the post params function, except this processes any uploaded
	 * files and sends them via cURL.
	 */
	private function setFiles() {
		if (sizeof($_FILES) == 0) {
			return;
		}
		
		foreach($_FILES as $key => $file) {
			$this->log->debug('Handling uploaded file: ' . $file['name']);
			
			if ($file['size'] == 0) {
				$this->log->debug('Size is zero -- ignoring');
				continue;
			}
			
			$path = $file['tmp_name'];
			
			if (is_uploaded_file($path)) {
				$newpath = dirname($path) . '/' . $file['name'];
				$this->log->debug('Moving file to: ' . $newpath);
				move_uploaded_file($path, $newpath);
				
				$this->post[$key] = '@' . $newpath;
				
				$this->cleanup_files[] = $newpath;
			}
		}
	}
	
	/** 
	 * Appends any GET parameters to the current URL. Ignores the
	 * 'url' parameters as this is passed by the proxy script.
	 */
	private function appendQueryString() {
		if (sizeof($_GET) <= 1) {
			return;
		}
	
		if (strpos($this->url, '?') === FALSE) {
			$this->url .= '?';
		}
		
		foreach($_GET as $key => $value) {
			if (in_array($key, array(URL_PARAM_NAME, 'proxy_username', 'proxy_password'))) {
				continue;
			}
			
			$this->url .= urlencode($key) . '=' . urlencode($value) . '&';
		}
		
		$this->log->debug(sprintf('Built query string [%s]', $this->url));
	}
	
	/**
	 * Include the nav bar in an HTML document.
	 */
	private function includeNavbar(& $html) {
		$this->log->debug('Including navbar: '. $this->url );
		// include() the file so that it doesn't have to be pure PHP
		// use the output buffer to prevent it being written to the page
		// in the wrong place.
		ob_start();
		include_once(dirname(__FILE__) . '/../navbar.inc.php');
		$navbar = ob_get_contents();
		ob_end_clean();
		
		if (preg_match('/<body.*?>/i', $html) !== FALSE) {
			$html = preg_replace('/<body(.*?)>/i', '<body$1>' . $navbar, $html);
		}
		else {
			// HTML Documents should have a <body> tag. However, if they don't,
			// prepend the navbar to the beginning of the document.
			$html = $navbar . $html;
		}
	}
	
	/** 
	 * Saves the username / password info (i.e. for Basic Authentication) to the user's cookie.
	 */
	private function storeUsernamePasswordInfo() {
		$cookieName = $this->sanitize($this->getBaseUrl()) . '_auth';
		
		$this->log->debug('Saving auth data');
		
		setcookie($cookieName, $this->username . ':' . $this->password);
	}
	
	/**
	 * Loads the username / password info for a site from the user's cookie.
	 */
	private function loadUsernamePasswordInfo() {
		$cookieName = $this->sanitize($this->getBaseUrl()) . '_auth';
		
		if (array_key_exists($cookieName, $_COOKIE)) {
			$value = $_COOKIE[$cookieName];
			$this->username = substr($value, 0, strpos($value, ':'));
			$this->password = substr($value, strpos($value, ':') + 1);
			
			$this->log->debug(sprintf('Loaded auth data for [%s]', $this->username));
		}
		else {
			$this->log->debug('No auth data');
		}
	}
	
	/**
	 * Check that the site is not on the blacklist / is on the whitelist, and 
	 * that the user is not banned.
	 */
	private function checkAccess() {
		global $WHITE_LIST, $BLACK_LIST, $BAN_LIST;
		
		$regex = '/(?:.*:\/\/)?(.*?)(?:\?|\/|$)/';
		
		$baseUrl = $this->url;
		$matches = array();
		preg_match($regex, $baseUrl, $matches);
		
		$domain = $matches[1];
		
		// Strip query string
		if (strpos($baseUrl, '?') !== FALSE) {
			$baseUrl = substr($baseUrl, 0, strpos($baseUrl, '?'));
		}
		
		if (sizeof($WHITE_LIST) > 0) {
			foreach($WHITE_LIST as $url) {
				$matches = array();
				preg_match($regex, $url, $matches);
				
				if ($this->checkDomainAccess($domain, $matches[1])) {
					return true;
				}
			}
			
			return 'Site is not on white list';
		}
		
		if (sizeof($BLACK_LIST) > 0) {
			foreach($BLACK_LIST as $url) {
				$matches = array();
				preg_match($regex, $url, $matches);
				
				if ($this->checkDomainAccess($domain, $matches[1])) {
					return 'Site is blacklisted';
				}
			}
		}
		
		if (sizeof($BAN_LIST) > 0) {
			$ip = $_SERVER['REMOTE_ADDR'];
			
			if (!empty($ip)) {
				foreach($BAN_LIST as $banned_ip) {
					if ($banned_ip == $ip || preg_match($banned_ip, $ip) == 0) {
						return 'IP is banned';
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Returns true if the check domain is part of the base domain. If the check domain starts with
	 * a '.' then this will match subdomains.
	 * For example:
	 *   +-----------------------------------------+
	 *   | base         | check           | return |
	 *   +-----------------------------------------+
	 *   | google.co.uk | google.co.uk    | true   |
	 *   | google.com   | mail.google.com | false  |
	 *   | .google.com  | mail.google.com | true   |
	 *   +-----------------------------------------+
	 */
	private function checkDomainAccess($base, $check) {
		if (substr($check, 0, 1) == '/') {
			$this->log->debug('Matches: ' . preg_match($check, $base));
			return preg_match($check, $base) != 0;
		}
		elseif (substr($check, 0, 1) == '.') {
			// starting with '.' means 'all subdomains'
			// so .google.com should match www.google.com, mail.google.com etc
			if (substr($check, 1) == $base || substr($base, -(strlen($check))) == $check) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return $base == $check;
		}
	}
	
	/**
	 * Returns a sanitized version of a piece of text, i.e. lowercased and converts all non-alphanumeric
	 * characters to underscores.
	 */
	private function sanitize($text) {
		$orig = $text;
		
		$text = strtolower($text);
		$text = preg_replace('/[^a-zA-Z0-9]+/', '_', $text);
		$text = preg_replace('/_$/', '', $text);
		
		//$this->log->debug("Sanitized '$orig' to '$text'");
		
		return $text;
	}
}

?>
